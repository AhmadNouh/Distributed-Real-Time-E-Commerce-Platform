import { PackageCheck, RotateCcw, Truck } from 'lucide-react'

const benefits = [
  { icon: PackageCheck, label: 'Curated quality' },
  { icon: Truck, label: 'Free shipping $100+' },
  { icon: RotateCcw, label: '30-day returns' },
]

export function LoginBrandPanel() {
  return (
    <section className="relative hidden min-h-screen overflow-hidden bg-[#171a38] px-11 py-11 text-white lg:flex lg:flex-col">
      <div className="relative z-10 flex items-center gap-2 text-[34px] font-semibold tracking-[-0.03em]">
        <span className="grid h-7 w-7 place-items-center rounded-md bg-white text-[#5146dc]">
          <PackageCheck size={17} strokeWidth={2.2} />
        </span>
        forma
      </div>

      <div className="relative z-10 mt-12 max-w-[410px]">
        <span className="inline-flex rounded-full bg-[#edeaff] px-2 py-1 text-[20px] font-medium text-[#5146dc]">
          A little more considered.
        </span>
        <h1 className="mt-4 text-[40px] font-semibold leading-[1.08] tracking-[-0.045em]">
          Good things.
          <br />
          Better everyday.
        </h1>
        <p className="mt-4 max-w-[390px] text-[20px] leading-5 text-[#aeb1c8]">
          Thoughtful essentials for the way you work, listen, and live. All in one place.
        </p>
      </div>

      <div className="relative mt-8 h-[268px] max-w-[406px] overflow-hidden rounded-xl bg-gradient-to-br from-[#d4d7c6] via-[#ede9dc] to-[#a5a58f] shadow-2xl">
        <div className="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-[#f2f0e2]/80 to-transparent" />
        <div className="absolute left-0 top-10 h-24 w-1/3 bg-[#f6f3e7]/80" />
        <div className="absolute bottom-0 left-0 right-0 h-24 bg-[#ae9472]" />
        <div className="absolute bottom-12 left-[12%] right-[8%] h-6 rounded-[50%] bg-[#8d7256] shadow-[0_13px_0_3px_#c3a680]" />
        <div className="absolute bottom-[70px] left-[28%] h-14 w-16 rounded-t-full bg-[#ced0bd] shadow-[8px_6px_0_#767a6a]" />
        <div className="absolute bottom-16 right-[22%] h-12 w-9 rounded-t-full bg-[#d9d4bf]" />
        <div className="absolute bottom-[72px] right-[16%] h-5 w-16 rounded-full bg-[#6e6c5a]" />
        <div className="absolute bottom-11 left-[44%] h-5 w-9 rounded bg-[#ebe9dc]" />
        <div className="absolute left-[46%] top-12 h-14 w-11 rotate-[-4deg] border-4 border-[#a58e6b] bg-[#e6e2d4]" />
      </div>

      <div className="relative z-10 mt-8 grid max-w-[406px] grid-cols-3 gap-3">
        {benefits.map(({ icon: Icon, label }) => (
          <div className="space-y-2 text-[20px] text-[#9fa3be]" key={label}>
            <Icon size={16} strokeWidth={1.4} className="text-[#aaa5e9]" />
            <p>{label}</p>
          </div>
        ))}
      </div>
      <p className="relative z-10 mt-auto text-[20px] text-[#737795]">
        © 2026 Forma Store. All rights reserved.
      </p>
      <div className="absolute -bottom-36 -right-36 h-80 w-80 rounded-full bg-[#4540a5]/20 blur-3xl" />
    </section>
  )
}
