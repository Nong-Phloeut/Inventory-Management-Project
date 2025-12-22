from app.database.db import get_connection

def analyze_stock(threshold=10):
    """
    Analyze current stock and return low-stock items.
    threshold: minimum quantity before alert
    """

    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            # Fetch products where current stock <= threshold
            cursor.execute("""
                SELECT s.product_id, p.name as product_name, s.quantity as stock
                FROM stocks s
                JOIN products p ON s.product_id = p.id
                WHERE s.quantity <= %s
            """, (threshold,))
            result = cursor.fetchall()
        return result
    finally:
        conn.close()
