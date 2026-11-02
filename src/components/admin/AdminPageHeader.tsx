import type { ReactNode } from "react";

export function AdminPageHeader({
  eyebrow,
  title,
  description,
  action,
}: {
  eyebrow?: string;
  title: string;
  description: string;
  action?: ReactNode;
}) {
  return (
    <div className="mb-7 flex items-start justify-between gap-4">
      <div>
        {eyebrow && (
          <p className="text-sm uppercase tracking-wide text-[#5146dc]">
            {eyebrow}
          </p>
        )}
        <h1 className="mt-1 text-2xl font-semibold tracking-[-.04em]">
          {title}
        </h1>
        <p className="mt-2 text-xs text-[#7e8798]">{description}</p>
      </div>
      {action}
    </div>
  );
}
