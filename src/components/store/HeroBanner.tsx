
import { PackageCheck, RotateCcw } from "lucide-react";

export function HeroBanner() {
  return (
    <section className="grid overflow-hidden rounded-lg bg-[#eeedff] md:grid-cols-[1.35fr_.95fr]">
      <div className="p-6 sm:p-8">
        <p className="text-sm font-semibold uppercase tracking-wide text-[#5146dc]">
          The everyday collection
        </p>

        <h1 className="mt-2 text-2xl font-semibold tracking-tight text-[#20283a] sm:text-3xl">
          A better space. A better day.
        </h1>

        <p className="mt-1 text-sm text-[#7b8394] sm:text-base">
          Considered essentials for wherever your day takes you.
        </p>

        <div className="mt-4 flex flex-wrap gap-4 text-xs text-[#4f5870] sm:text-sm">
          <span className="flex items-center gap-1">
            <PackageCheck size={14} className="shrink-0 text-[#5146dc]" />
            Free shipping on orders $100+
          </span>

          <span className="flex items-center gap-1">
            <RotateCcw size={14} className="shrink-0 text-[#5146dc]" />
            30-day returns
          </span>
        </div>
      </div>

      <div className="min-h-[130px] bg-[url('https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=1000&q=85')] bg-cover bg-center" />
    </section>
  );
}
