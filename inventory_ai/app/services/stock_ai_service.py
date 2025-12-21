import pandas as pd
from datetime import datetime
from app.database.db import get_connection

def fetch_stock_history(product_id):
    """
    Fetch stock history for a product.
    Assumes your table tracks stock changes over time.
    """
    query = f"""
        SELECT record_date, stock_quantity
        FROM stock_history
        WHERE product_id = {product_id}
        ORDER BY record_date
    """
    conn = get_connection()
    df = pd.read_sql(query, conn)
    conn.close()
    return df


def calculate_daily_depletion(df):
    """
    Calculate average daily stock depletion rate.
    """
    df['record_date'] = pd.to_datetime(df['record_date'])
    df = df.sort_values('record_date')

    # Calculate stock difference
    df['stock_diff'] = df['stock_quantity'].diff() * -1

    # Remove increases (purchases)
    depletion = df[df['stock_diff'] > 0]

    if depletion.empty:
        return 0

    days = (df['record_date'].iloc[-1] - df['record_date'].iloc[0]).days
    total_depleted = depletion['stock_diff'].sum()

    return total_depleted / max(days, 1)


def stock_ai_analysis(product_id, current_stock, lead_time_days=7, coverage_days=30):
    """
    AI logic using stock depletion only (NO SALES).
    """
    df = fetch_stock_history(product_id)

    daily_depletion = calculate_daily_depletion(df)

    if daily_depletion == 0:
        return {
            "status": "insufficient_data",
            "message": "Not enough data to predict stock depletion"
        }

    days_until_stockout = round(current_stock / daily_depletion, 1)

    reorder_needed = days_until_stockout <= lead_time_days

    recommended_quantity = round(daily_depletion * coverage_days)

    stockout_date = (
        datetime.today()
        .date()
        .fromordinal(
            datetime.today().toordinal() + int(days_until_stockout)
        )
    )

    return {
        "daily_depletion_rate": round(daily_depletion, 2),
        "days_until_stockout": days_until_stockout,
        "predicted_stockout_date": str(stockout_date),
        "reorder_needed": reorder_needed,
        "recommended_purchase_quantity": recommended_quantity
    }
