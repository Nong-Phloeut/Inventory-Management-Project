from sqlalchemy.orm import Session
from app.models.stock import Stock
from app.models.product import Product


def recommend_purchase(
    db: Session,
    reorder_threshold: int = 20,
    target_stock: int = 50
):
    """
    Recommend purchase orders grouped by best supplier
    """

    results = (
        db.query(
            Stock.product_id,
            Stock.quantity.label("stock"),
            Product.name.label("product_name"),
            Product.supplier_id
        )
        .join(Product, Stock.product_id == Product.id)
        .all()
    )

    supplier_map: dict[int, list] = {}

    for r in results:
        if r.stock < reorder_threshold:
            reorder_qty = target_stock - r.stock

            supplier_map.setdefault(r.supplier_id, []).append({
                "product_id": r.product_id,
                "product_name": r.product_name,
                "reorder_qty": reorder_qty
            })

    if not supplier_map:
        return {
            "supplier_id": None,
            "items": []
        }

    best_supplier_id = max(
        supplier_map,
        key=lambda supplier_id: len(supplier_map[supplier_id])
    )

    return {
        "supplier_id": best_supplier_id,
        "items": supplier_map[best_supplier_id]
    }
