from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
from app.database.db import get_db
from app.services.product_service import list_products_with_stock

router = APIRouter(
    prefix="/products",
    tags=["Products"]
)


@router.get("")
def get_all_products(db: Session = Depends(get_db)):
    return list_products_with_stock(db)
