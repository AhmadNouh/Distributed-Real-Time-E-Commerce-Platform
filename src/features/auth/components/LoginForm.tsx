import { ArrowRight, Eye, EyeOff, LockKeyhole } from 'lucide-react'
import type { FormEvent } from 'react'
import { Button, TextField } from '../../../components/ui'

type LoginFormProps = {
  email: string
  password: string
  showPassword: boolean
  onEmailChange: (email: string) => void
  onPasswordChange: (password: string) => void
  onPasswordVisibilityChange: () => void
  onSubmit: () => void
  isSubmitting: boolean
  error: string | null
}

export function LoginForm({
  email,
  password,
  showPassword,
  onEmailChange,
  onPasswordChange,
  onPasswordVisibilityChange,
  onSubmit,
  isSubmitting,
  error,
}: LoginFormProps) {
  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    onSubmit()
  }

  return (
    <div className="mx-auto flex w-full max-w-[462px] flex-1 flex-col justify-center py-16">
      <div>
        <h2 className="text-[32px] font-semibold tracking-[-0.035em] text-[#20283a]">Welcome back</h2>
        <p className="mt-1 text-xs text-[#81899b]">Sign in to your Forma account to continue.</p>
      </div>

      <form className="mt-6 space-y-5" onSubmit={handleSubmit}>
        <TextField id="email" label="Email address" type="email" value={email} onChange={(event) => onEmailChange(event.target.value)} required />
        <label className="block space-y-2" htmlFor="password">
          <span className="block text-xs font-medium text-[#273044]">Password</span>
          <span className="relative block">
            <input
              id="password"
              className="h-11 w-full rounded-md border border-[#e1e4eb] bg-white px-3 pr-10 text-sm text-[#273044] outline-none transition focus:border-[#776cff] focus:ring-4 focus:ring-[#5146dc]/10"
              type={showPassword ? 'text' : 'password'}
              value={password}
              onChange={(event) => onPasswordChange(event.target.value)}
              required
            />
            <button type="button" onClick={onPasswordVisibilityChange} className="absolute right-3 top-1/2 -translate-y-1/2 text-[#8992a4]" aria-label="Toggle password visibility">
              {showPassword ? <EyeOff size={15} /> : <Eye size={15} />}
            </button>
          </span>
        </label>

        <div className="flex items-center justify-between text-[20px] text-[#7e8798]">
          <label className="flex items-center gap-2">
            <input type="checkbox" defaultChecked className="h-3 w-3 rounded border-[#cbd0dc] accent-[#5146dc]" />
            Remember me for 30 days
          </label>
          <button type="button" className="font-medium text-[#5146dc] hover:underline">Forgot password?</button>
        </div>

        {error && <p role="alert" className="rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">{error}</p>}

        <Button type="submit" disabled={isSubmitting} className="mt-1 h-11 w-full gap-2">
          <ArrowRight size={14} />
          {isSubmitting ? 'Signing in...' : 'Sign in'}
        </Button>
      </form>

      <div className="mt-5 flex items-center justify-center gap-2 text-[20px] text-[#9aa1b0]">
        <LockKeyhole size={12} />
        Secure access for customers and administrators
      </div>
    </div>
  )
}
