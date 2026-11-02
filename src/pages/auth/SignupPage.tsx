import { LoginBrandPanel } from '../../features/auth/components/LoginBrandPanel'
import { LoginFooter } from '../../features/auth/components/LoginFooter'
import { SignupForm } from '../../features/auth/components/SignupForm'
import { SignupHeader } from '../../features/auth/components/SignupHeader'
import { useSignupForm } from '../../features/auth/useSignupForm'

export function SignupPage() {
  const signupForm = useSignupForm()

  return (
    <div className="min-h-screen bg-white text-[#20283a] lg:grid lg:grid-cols-[44.5%_55.5%]">
      <LoginBrandPanel />
      <section className="flex min-h-screen flex-col px-6 py-7 sm:px-12 lg:px-[8.1%] lg:py-8">
        <SignupHeader />
        <SignupForm
          name={signupForm.name}
          email={signupForm.email}
          password={signupForm.password}
          passwordConfirmation={signupForm.passwordConfirmation}
          showPassword={signupForm.showPassword}
          showPasswordConfirmation={signupForm.showPasswordConfirmation}
          isSubmitting={signupForm.isSubmitting}
          error={signupForm.error}
          onNameChange={signupForm.setName}
          onEmailChange={signupForm.setEmail}
          onPasswordChange={signupForm.setPassword}
          onPasswordConfirmationChange={signupForm.setPasswordConfirmation}
          onPasswordVisibilityChange={() => signupForm.setShowPassword(!signupForm.showPassword)}
          onPasswordConfirmationVisibilityChange={() => signupForm.setShowPasswordConfirmation(!signupForm.showPasswordConfirmation)}
          onSubmit={signupForm.submit}
        />
        <LoginFooter />
      </section>
    </div>
  )
}
