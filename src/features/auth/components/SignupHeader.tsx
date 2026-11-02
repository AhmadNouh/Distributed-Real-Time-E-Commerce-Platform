import { ArrowRight } from 'lucide-react'
import { Link } from 'react-router'

export function SignupHeader() {
  return (
    <header className="flex justify-end text-[20px] text-[#7c8496]">
      Already have an account?
      <Link to="/login" className="ml-1 font-semibold text-[#5146dc] hover:underline">
        Sign in <ArrowRight className="inline-block" size={11} />
      </Link>
    </header>
  )
}
