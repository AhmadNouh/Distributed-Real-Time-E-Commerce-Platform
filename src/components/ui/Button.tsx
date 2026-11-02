import type { ButtonHTMLAttributes, PropsWithChildren } from 'react'

type ButtonProps = PropsWithChildren<ButtonHTMLAttributes<HTMLButtonElement>> & {
  variant?: 'primary' | 'ghost'
}

export function Button({
  children,
  className = '',
  variant = 'primary',
  ...props
}: ButtonProps) {
  const styles =
    variant === 'primary'
      ? 'bg-[#5146dc] text-white shadow-[0_8px_18px_rgba(81,70,220,0.18)] hover:bg-[#4439cb]'
      : 'text-[#5146dc] hover:bg-[#f1efff]'

  return (
    <button
      className={`inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-60 ${styles} ${className}`}
      {...props}
    >
      {children}
    </button>
  )
}
