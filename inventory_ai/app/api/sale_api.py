from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.database.db import get_db
from app.services.sale_service import create_sale
from pydantic import BaseModel
from typing import List
from app.database.schemas.sale import SaleCreate

router = APIRouter(prefix="/sales", tags=["Sales"])

@router.post("")
def make_sale(sale: SaleCreate, db: Session = Depends(get_db)):
    try:
        result = create_sale(db, sale.dict())
        return {"sale": result, "message": "Sale saved successfully"}
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail="Failed to save sale")
