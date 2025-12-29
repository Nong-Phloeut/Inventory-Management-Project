from pydantic import BaseModel
from typing import List

class SaleItemCreate(BaseModel):
    product_id: int
    qty: int
    price: float

class SaleCreate(BaseModel):
    items: List[SaleItemCreate]
    total: float
