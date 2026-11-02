import { Navigate, Route, Routes } from 'react-router'
import { AdminGuard } from '../components/common/AdminGuard'
import { AdminLayout } from '../layouts/AdminLayout'
import { StoreLayout } from '../layouts/StoreLayout'
import { AdminCustomersPage } from '../pages/admin/AdminCustomersPage'
import { AdminDashboardPage } from '../pages/admin/AdminDashboardPage'
import { AdminOrdersPage } from '../pages/admin/AdminOrdersPage'
import { AdminProductsPage } from '../pages/admin/AdminProductsPage'
import { LoginPage } from '../pages/auth/LoginPage'
import { SignupPage } from '../pages/auth/SignupPage'
import { CartPage } from '../pages/store/CartPage'
import { HomePage } from '../pages/store/HomePage'
import { AdminProductCreatePage } from '../pages/admin/AdminProductCreatePage'
import { ProductDetailsPage } from '../pages/store/ProductDetailsPage'
import { OrdersPage } from '../pages/store/OrdersPage'
import { AdminInventoryPage } from '../pages/admin/AdminInventoryPage'

export function AppRouter() {
  return (
    <Routes>
      <Route path="login" element={<LoginPage />} />
      <Route path="signup" element={<SignupPage />} />
      <Route element={<AdminGuard />}>
        <Route path="admin" element={<AdminLayout />}>
          <Route index element={<AdminDashboardPage />} />
          <Route path="products" element={<AdminProductsPage />} />
          <Route path="orders" element={<AdminOrdersPage />} />
          <Route path="customers" element={<AdminCustomersPage />} />
          <Route path="products/new" element={<AdminProductCreatePage />} />
          <Route path="inventory" element={<AdminInventoryPage />} />
        </Route>
      </Route>
      
      <Route element={<StoreLayout />}>
        <Route index element={<Navigate to="/store" replace />} />
        <Route path="store" element={<HomePage />} />
        <Route path="store/cart" element={<CartPage />} />
        <Route path="store/products/:id" element={<ProductDetailsPage />} />
        <Route path="store/orders" element={<OrdersPage />} />
      </Route>
    </Routes>
  )
}
