from sqlalchemy.orm import Session
from app.models.sale import Sale
from app.models.sale_item import SaleItem
from app.models.stock import Stock
from datetime import datetime

def create_sale(db: Session, sale_data: dict):
    items = sale_data.get("items", [])
    total = sale_data.get("total", 0)

    if not items:
        raise ValueError("No items in sale")

    try:
        sale = Sale(total=total)
        db.add(sale)
        db.commit()
        db.refresh(sale)

        for item in items:
            sale_item = SaleItem(
                sale_id=sale.id,
                product_id=item["product_id"],
                quantity=item["qty"],
                price=item["price"]
            )
            db.add(sale_item)

            # update stock
            stock = db.query(Stock).filter(Stock.product_id == item["product_id"]).first()
            if stock:
                stock.qty -= item["qty"]
                if stock.qty < 0:
                    stock.qty = 0

        db.commit()
        return {"sale_id": sale.id, "total": total}
    except Exception as e:
        db.rollback()  # important! rollback if something goes wrong
        print("Sale creation error:", e)  # log for debugging
        raise e
