import type { ReactNode } from "react";

export function StatCard({
  label,
  value,
  note,
  icon,
}: {
  label: string;
  value: string;
  note: string;
  icon: ReactNode;
}) {
  return (
    <div className="rounded-lg border border-[#e2e5eb] bg-white p-5">
      <div className="flex items-center justify-between text-sm text-[#7e8798]">
        {label}
        <span className="text-[#8992a4]">{icon}</span>
      </div>
      <strong className="mt-3 block text-2xl tracking-[-.04em]">
        {value}
      </strong>
      <p className="mt-3 text-xs text-[#6c9d77]">{note}</p>
    </div>
  );
}
