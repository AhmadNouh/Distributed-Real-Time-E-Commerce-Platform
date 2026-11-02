import { MoreHorizontal, Pencil } from 'lucide-react'
import { useEffect, useState } from 'react'
import { productsApi } from '../../features/products/productsApi'
import type { Product } from '../../features/products/product.types'

function extractProducts(rawData: any): Product[] {
  if (Array.isArray(rawData)) return rawData
  if (Array.isArray(rawData?.data)) return rawData.data
  if (Array.isArray(rawData?.products)) return rawData.products
  if (Array.isArray(rawData?.items)) return rawData.items
  return []
}

export function ProductTable() {
  const [products, setProducts] = useState<Product[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetch() {
      try {
        const res = await productsApi.list()
        setProducts(extractProducts(res.data))
      } catch (e) {
        console.error(e)
      } finally {
        setLoading(false)
      }
    }
    fetch()
  }, [])

  if (loading) {
    return <div className="p-5 text-base text-[#8992a4]">Loading...</div>
  }

  return <div className="overflow-x-auto"><table className="w-full min-w-[760px] text-left text-base"><thead className="bg-[#f7f8fa] text-[#7e8798]"><tr>{['Product', 'SKU', 'Category', 'Price', 'Available', 'Status', ''].map((head) => <th className="px-5 py-4 font-medium" key={head}>{head}</th>)}</tr></thead><tbody>{products.map((product, index) => <tr className="border-t border-[#edf0f3]" key={product.id}><td className="flex items-center gap-3 px-5 py-4"><img src={(product as any).image || '/vite.svg'} className="h-11 w-11 rounded object-cover" alt="" /><span><b>{product.name}</b><small className="mt-1 block text-[#8992a4]">{product.description}</small></span></td><td className="px-5 py-4 text-[#8992a4]">FR-{String(index + 1).padStart(2, '0')}0{index + 1}</td><td className="px-5 py-4 text-[#667187]">{(product as any).categoryName || product.category_id}</td><td className="px-5 py-4">${Number(product.price).toFixed(2)}</td><td className="px-5 py-4">{product.stock} units</td><td className="px-5 py-4"><span className={`rounded-full px-2.5 py-1 text-base ${product.stock === 0 ? 'bg-[#fff0f0] text-[#c84848]' : product.stock < 5 ? 'bg-[#fff8e6] text-[#b47616]' : 'bg-[#eaf9f0] text-[#26834d]'}`}>● {product.stock === 0 ? 'Out of stock' : product.stock < 5 ? 'Low stock' : 'In stock'}</span></td><td className="px-5 py-4"><button><Pencil size={15} /></button><button className="ml-3"><MoreHorizontal size={15} /></button></td></tr>)}</tbody></table></div>
}
