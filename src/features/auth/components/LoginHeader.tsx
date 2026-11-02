import { ArrowRight } from 'lucide-react'
import { Link } from 'react-router'

export function LoginHeader() {
  return (
    <header className="flex justify-end text-[20px] text-[#7c8496]">
      New to Forma?
      <Link to="/signup" className="ml-1 font-semibold text-[#5146dc] hover:underline">
        Create an account <ArrowRight className="inline-block" size={11} />
      </Link>
    </header>
  )
}
