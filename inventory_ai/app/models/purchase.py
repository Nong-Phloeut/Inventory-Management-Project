from sqlalchemy import Column, Integer, ForeignKey, DateTime, String
from sqlalchemy.sql import func
from app.database.db import Base
from sqlalchemy.orm import relationship

class Purchase(Base):
    __tablename__ = "purchases"

    id = Column(Integer, primary_key=True)
    purchase_status_id = Column(Integer)
    purchase_by = Column(Integer, ForeignKey("users.id"))
    note = Column(String)
    items = relationship(
        "PurchaseItem",
        backref="purchase",
        cascade="all, delete"
    )
    supplier_id = Column(Integer, ForeignKey("suppliers.id"))
    supplier = relationship("Supplier")

    created_at = Column(DateTime(timezone=True), server_default=func.now())
