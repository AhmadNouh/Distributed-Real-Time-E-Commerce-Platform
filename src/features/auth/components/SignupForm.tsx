import { ArrowRight, Eye, EyeOff, LockKeyhole } from 'lucide-react'
import type { FormEvent } from 'react'
import { Button, TextField } from '../../../components/ui'

type SignupFormProps = {
  name: string
  email: string
  password: string
  passwordConfirmation: string
  showPassword: boolean
  showPasswordConfirmation: boolean
  isSubmitting: boolean
  error: string | null
  onNameChange: (value: string) => void
  onEmailChange: (value: string) => void
  onPasswordChange: (value: string) => void
  onPasswordConfirmationChange: (value: string) => void
  onPasswordVisibilityChange: () => void
  onPasswordConfirmationVisibilityChange: () => void
  onSubmit: () => void
}

export function SignupForm({
  name,
  email,
  password,
  passwordConfirmation,
  showPassword,
  showPasswordConfirmation,
  isSubmitting,
  error,
  onNameChange,
  onEmailChange,
  onPasswordChange,
  onPasswordConfirmationChange,
  onPasswordVisibilityChange,
  onPasswordConfirmationVisibilityChange,
  onSubmit,
}: SignupFormProps) {
  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    onSubmit()
  }

  return (
    <div className="mx-auto flex w-full max-w-[462px] flex-1 flex-col justify-center py-12">
      <div>
        <h2 className="text-[32px] font-semibold tracking-[-0.035em] text-[#20283a]">Make yourself at home</h2>
        <p className="mt-1 text-xs text-[#81899b]">Create your account. A better everyday starts here.</p>
      </div>

      <form className="mt-5 space-y-4" onSubmit={handleSubmit}>
        <TextField id="name" label="Full name" value={name} onChange={(event) => onNameChange(event.target.value)} required />
        <TextField id="email" label="Email address" type="email" value={email} onChange={(event) => onEmailChange(event.target.value)} required />

        <PasswordField
          id="password"
          label="Password"
          value={password}
          placeholder="Create a strong password"
          visible={showPassword}
          onChange={onPasswordChange}
          onToggle={onPasswordVisibilityChange}
        />
        <p className="-mt-2 text-[20px] text-[#8b94a5]">Use at least 8 characters, one number and one symbol.</p>

        <PasswordField
          id="password-confirmation"
          label="Confirm password"
          value={passwordConfirmation}
          placeholder="Re-enter your password"
          visible={showPasswordConfirmation}
          onChange={onPasswordConfirmationChange}
          onToggle={onPasswordConfirmationVisibilityChange}
        />

        <label className="flex items-center gap-2 text-[20px] text-[#7e8798]">
          <input type="checkbox" required className="h-3 w-3 rounded border-[#cbd0dc] accent-[#5146dc]" />
          I agree to the Terms of service and Privacy policy.
        </label>

        {error && <p role="alert" className="rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">{error}</p>}

        <Button type="submit" disabled={isSubmitting} className="h-11 w-full gap-2">
          <ArrowRight size={14} />
          {isSubmitting ? 'Creating account...' : 'Create account'}
        </Button>
      </form>

      <div className="mt-5 flex items-center justify-center gap-2 text-[20px] text-[#9aa1b0]">
        <LockKeyhole size={12} />
        Your account is protected with secure authentication
      </div>
    </div>
  )
}

type PasswordFieldProps = {
  id: string
  label: string
  value: string
  placeholder: string
  visible: boolean
  onChange: (value: string) => void
  onToggle: () => void
}

function PasswordField({ id, label, value, placeholder, visible, onChange, onToggle }: PasswordFieldProps) {
  return (
    <label className="block space-y-2" htmlFor={id}>
      <span className="block text-xs font-medium text-[#273044]">{label}</span>
      <span className="relative block">
        <input
          id={id}
          className="h-11 w-full rounded-md border border-[#e1e4eb] bg-white px-3 pr-10 text-sm text-[#273044] outline-none transition placeholder:text-[#aeb6c5] focus:border-[#776cff] focus:ring-4 focus:ring-[#5146dc]/10"
          type={visible ? 'text' : 'password'}
          value={value}
          placeholder={placeholder}
          onChange={(event) => onChange(event.target.value)}
          minLength={8}
          required
        />
        <button type="button" onClick={onToggle} className="absolute right-3 top-1/2 -translate-y-1/2 text-[#8992a4]" aria-label={`Toggle ${label.toLowerCase()} visibility`}>
          {visible ? <EyeOff size={15} /> : <Eye size={15} />}
        </button>
      </span>
    </label>
  )
}
