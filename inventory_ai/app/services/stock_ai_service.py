from sqlalchemy.orm import Session
from app.models.stock import Stock
from app.models.product import Product


def analyze_stock(
    db: Session,
    threshold: int = 10
):
    """
    Analyze current stock and return low-stock items.
    """

    results = (
        db.query(
            Stock.product_id,
            Product.name.label("product_name"),
            Stock.qty.label("stock")
        )
        .join(Product, Stock.product_id == Product.id)
        .filter(Stock.qty <= threshold)
        .all()
    )

    return [
        {
            "product_id": r.product_id,
            "product_name": r.product_name,
            "stock": r.stock
        }
        for r in results
    ]
