from sqlalchemy.orm import Session
from app.models.product import Product


from sqlalchemy.orm import Session
from app.models.product import Product
from app.models.stock import Stock


def list_products_with_stock(db: Session):
    """
    Return all products with stock quantity
    """
    results = (
        db.query(
            Product.id,
            Product.name,
            Product.price,
            Product.category_id,
            Stock.qty
        )
        .join(Stock, Stock.product_id == Product.id)
        .all()
    )

    return [
        {
            "id": r.id,
            "name": r.name,
            "price": r.price,
            "category_id": r.category_id,
            "stock": r.qty,
            "image": "https://thepantrysa.com/cdn/shop/files/2x_1200x1200.png?v=1691074793"
        }
        for r in results
    ]
