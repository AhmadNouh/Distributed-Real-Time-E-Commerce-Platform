import { useState, useRef, useEffect } from 'react';
import { LogOut, User, ChevronDown } from 'lucide-react';
import { useAuthStore } from '../../features/auth/authStore';
import { useNavigate } from 'react-router';

export function UserDropdown() {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);
  
  const user = useAuthStore((state) => state.user);
  const token = useAuthStore((state) => state.token);
  const logout = useAuthStore((state) => state.logout);
  const navigate = useNavigate();

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleLogout = () => {
    logout();
    setIsOpen(false);
    navigate('/login', { replace: true });
  };

  if (!user && !token) {
    return (
      <button
        onClick={() => navigate('/login')}
        className="flex items-center gap-2 rounded-full border border-[#e2e5eb] bg-white px-3 py-1.5 text-sm font-medium text-[#20283a] transition hover:bg-gray-50"
      >
        <User size={14} />
        <span>Login</span>
      </button>
    );
  }

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="flex items-center gap-2 rounded-full border border-[#e2e5eb] bg-white px-3 py-1.5 text-sm font-medium text-[#20283a] transition hover:bg-gray-50"
      >
        <div className="grid h-7 w-7 place-items-center rounded-full bg-[#eeedff] text-[#5146dc]">
          <User size={14} />
        </div>
        <span>{user?.name || user?.email || 'Account'}</span>
        <ChevronDown size={14} className={`transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`} />
      </button>

      {isOpen && (
        <div className="absolute right-0 mt-2 w-48 rounded-lg border border-[#e2e5eb] bg-white py-1 shadow-lg z-50">
          <div className="px-4 py-2 border-b border-gray-100">
            <p className="text-xs text-gray-500">Signed in as</p>
            <p className="text-sm font-semibold text-[#20283a] truncate">{user?.email}</p>
          </div>

          <button
            onClick={handleLogout}
            className="flex w-full items-center gap-2 px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50 transition"
          >
            <LogOut size={14} />
            Logout
          </button>
        </div>
      )}
    </div>
  );
}