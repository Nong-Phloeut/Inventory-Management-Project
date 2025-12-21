import pandas as pd
from prophet import Prophet
from app.database.db import get_connection

def fetch_sales_data(product_id):
    """
    Fetch historical monthly sales data for a given product from the database.
    
    Args:
        product_id (int): ID of the product to fetch sales for.
    
    Returns:
        pd.DataFrame: DataFrame with columns 'ds' (date) and 'y' (sales count)
    """
    query = f"""
        SELECT sale_date AS ds, quantity AS y
        FROM sales
        WHERE product_id = {product_id}
        ORDER BY sale_date
    """
    conn = get_connection()
    df = pd.read_sql(query, conn)
    conn.close()
    
    return df

def forecast_sales(product_id, months=1, current_stock=0):
    """
    Forecast future sales for a product and calculate reorder recommendation.
    
    Args:
        product_id (int): Product ID
        months (int): How many months to forecast
        current_stock (int): Current stock level
    
    Returns:
        dict: {
            'forecast': list of future predictions,
            'current_stock': int,
            'reorder_needed': bool,
            'recommended_order_quantity': int
        }
    """
    # Step 1: Fetch historical sales data
    df = fetch_sales_data(product_id)
    
    if df.empty:
        return {
            "forecast": [],
            "current_stock": current_stock,
            "reorder_needed": False,
            "recommended_order_quantity": 0
        }
    
    # Step 2: Train Prophet model
    model = Prophet()
    model.fit(df)
    
    # Step 3: Predict future sales
    future = model.make_future_dataframe(periods=months, freq='M')
    forecast = model.predict(future)
    
    # Step 4: Take only forecasted months
    forecast_result = forecast[['ds', 'yhat', 'yhat_lower', 'yhat_upper']].tail(months)
    
    # Step 5: Calculate reorder recommendation
    predicted_sales = forecast_result['yhat'].sum()
    recommended_order = max(0, predicted_sales - current_stock)
    
    return {
        "forecast": forecast_result.to_dict(orient='records'),
        "current_stock": current_stock,
        "reorder_needed": recommended_order > 0,
        "recommended_order_quantity": round(recommended_order)
    }
