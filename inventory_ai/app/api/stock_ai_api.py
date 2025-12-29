from fastapi import APIRouter, Depends, Query
from sqlalchemy.orm import Session
from app.database.db import get_db
from app.services.stock_ai_service import analyze_stock

router = APIRouter(prefix="/stock-ai", tags=["Stock AI"])


@router.get("/low-stock")
def get_low_stock(
    threshold: int = Query(10, ge=0),
    db: Session = Depends(get_db)
):
    return analyze_stock(db=db, threshold=threshold)
