import { LoginBrandPanel } from '../../features/auth/components/LoginBrandPanel'
import { LoginFooter } from '../../features/auth/components/LoginFooter'
import { LoginForm } from '../../features/auth/components/LoginForm'
import { LoginHeader } from '../../features/auth/components/LoginHeader'
import { useLoginForm } from '../../features/auth/useLoginForm'

export function LoginPage() {
  const loginForm = useLoginForm()

  return (
    <div className="min-h-screen bg-white text-[#20283a] lg:grid lg:grid-cols-[44.5%_55.5%]">
      <LoginBrandPanel />
      <section className="flex min-h-screen flex-col px-6 py-7 sm:px-12 lg:px-[8.1%] lg:py-8">
        <LoginHeader />
        <LoginForm
          email={loginForm.email}
          password={loginForm.password}
          showPassword={loginForm.showPassword}
          onEmailChange={loginForm.setEmail}
          onPasswordChange={loginForm.setPassword}
          onPasswordVisibilityChange={() => loginForm.setShowPassword(!loginForm.showPassword)}
          onSubmit={loginForm.submit}
          isSubmitting={loginForm.isSubmitting}
          error={loginForm.error}
        />
        <LoginFooter />
      </section>
    </div>
  )
}
