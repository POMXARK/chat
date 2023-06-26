"""add chat table

Revision ID: 89face2f5c24
Revises: 07c71f4389b6
Create Date: 2023-06-23 14:49:34.807861

"""
from alembic import op
import sqlalchemy as sa
from sqlalchemy.dialects import postgresql

# revision identifiers, used by Alembic.
revision = "89face2f5c24"
down_revision = "07c71f4389b6"
branch_labels = None
depends_on = None


def upgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.create_table(
        "chat",
        sa.Column("id", sa.INTEGER(), autoincrement=True, nullable=False),
        sa.Column("stmt_id", sa.INTEGER(), nullable=False),
        sa.Column("seller_id", sa.INTEGER(), nullable=False),
        sa.Column("history", postgresql.JSON(astext_type=sa.Text()), nullable=False),
        sa.Column("is_active", sa.BOOLEAN(), nullable=False),
        sa.PrimaryKeyConstraint("id"),
    )
    # ### end Alembic commands ###


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.drop_table("chat")
    # ### end Alembic commands ###
