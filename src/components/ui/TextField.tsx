import type { InputHTMLAttributes } from 'react'

type TextFieldProps = InputHTMLAttributes<HTMLInputElement> & {
  label: string
}

export function TextField({ label, id, className = '', ...props }: TextFieldProps) {
  return (
    <label className="block space-y-2" htmlFor={id}>
      <span className="block text-xs font-medium text-[#273044]">{label}</span>
      <input
        id={id}
        className={`h-11 w-full rounded-md border border-[#e1e4eb] bg-white px-3 text-sm text-[#273044] outline-none transition placeholder:text-[#9aa1b1] focus:border-[#776cff] focus:ring-4 focus:ring-[#5146dc]/10 ${className}`}
        {...props}
      />
    </label>
  )
}
