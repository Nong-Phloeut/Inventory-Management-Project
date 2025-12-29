from sqlalchemy import Column, Integer, Float, DateTime, String
from sqlalchemy.sql import func
from app.database.db import Base
from sqlalchemy.orm import relationship
class Sale(Base):
    __tablename__ = "sales"

    id = Column(Integer, primary_key=True)
    customer_name = Column(String)
    total = Column(Float)

    created_at = Column(DateTime(timezone=True), server_default=func.now())

    items = relationship("SaleItem", back_populates="sale")
