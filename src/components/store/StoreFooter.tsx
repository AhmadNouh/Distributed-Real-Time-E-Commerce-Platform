
export function StoreFooter() {
  return (
    <footer className="border-t border-[#e4e6ec] bg-white">
      <div className="mx-auto flex max-w-[1240px] flex-wrap items-center justify-between gap-3 px-5 py-5 text-xs text-[#7c8496] sm:text-sm">
        <span>
          © 2026 Forma. Thoughtfully selected, made for your everyday.
        </span>

        <div className="flex flex-wrap gap-4 sm:gap-5">
          <a href="#help">Help center</a>
          <a href="#shipping">Shipping & returns</a>
          <a href="#privacy">Privacy</a>
        </div>
      </div>
    </footer>
  )
}
