from sqlalchemy import Column, Integer, ForeignKey, DateTime
from sqlalchemy.sql import func
from database import Base

class Stock(Base):
    __tablename__ = "stocks"

    id = Column(Integer, primary_key=True)
    product_id = Column(Integer, ForeignKey("products.id"))
    qty = Column(Integer)
    draft_qty = Column(Integer)

    created_at = Column(DateTime(timezone=True), server_default=func.now())
