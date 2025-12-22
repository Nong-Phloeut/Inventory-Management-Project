from app.database.db import get_connection

from app.database.db import get_connection

def recommend_purchase(reorder_threshold=20, target_stock=50):
    """
    Suggest products to reorder, but only from the best supplier.
    Criteria: supplier with most products below threshold.
    
    Returns a dictionary:
    {
        "supplier_id": 1,
        "items": [
            {"product_id": 3, "product_name": "sdf", "reorder_qty": 39},
            ...
        ]
    }
    """
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            # Get current stock and supplier info
            cursor.execute("""
                SELECT s.product_id, p.name as product_name, s.quantity as stock,
                       p.supplier_id
                FROM stocks s
                JOIN products p ON s.product_id = p.id
            """)
            products = cursor.fetchall()

        # Group products by supplier_id
        supplier_map = {}
        for p in products:
            if p['stock'] < reorder_threshold:
                sid = p['supplier_id']
                if sid not in supplier_map:
                    supplier_map[sid] = []
                supplier_map[sid].append({
                    "product_id": p['product_id'],
                    "product_name": p['product_name'],
                    "reorder_qty": target_stock - p['stock']
                })

        if not supplier_map:
            return {"supplier_id": None, "items": []}

        # Pick the supplier with the most low-stock products
        best_supplier_id = max(supplier_map, key=lambda k: len(supplier_map[k]))
        return {
            "supplier_id": best_supplier_id,
            "items": supplier_map[best_supplier_id]
        }
    finally:
        conn.close()
