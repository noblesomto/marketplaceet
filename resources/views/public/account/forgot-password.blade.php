@include('public.layouts.header')

<div class="bg-white h-screen">
  <section class="w-full flex justify-center items-center h-24 border-b-2 border-b-gray-400 shadow-lg shadow-b-2.5 shadow-gray-300">
    <a href="/"><img src="{{ asset('frontend/images/logo.png') }}" class="h-9" alt="Marketplace Ethiopia Logo"></a>
  </section>

  <section class="w-full lg:w-2/6 mx-auto bg-white p-6 lg:p-10 mt-1 lg:mt-10 rounded-[20px] shadow-lg">
    <div class="w-full mx-auto">
      @include('public.components.flash-message')
      <form method="POST" action="/forgot-password">
        @csrf
        <div class="mb-5">
          <h2 class="font-bold text-xl mt-5 w-full">Forgot Password</h2>
          <p class="text-base mt-3">Enter your email address</p>
        </div>

        <div class="mb-6 mt-3 lg:mt-10">
          @if ($errors->has('email'))
            <span class="text-red-900 my-1">{{ $errors->first('email') }}</span>
          @endif
          <label class="block mb-2">Email *</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" 
                 class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base md:text-lg"
                 value="{{ old('email') }}" required>
        </div>


        <div class="mt-8">
          <button type="submit" class="w-full bg-secondary-200 hover:bg-secondary-100 text-lg text-dark_green font-black py-2 px-2 rounded-full flex justify-center items-center">
            <span>Submit</span>
            <span class="ml-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
              </svg>
            </span>
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 text-lg mt-4">
          <div>Not registered yet?</div>
          <div><a class="mx-2 font-black underline" href="/register">Create an account</a></div>
        </div>
      </form>
    </div>
  </section>
</div>

<script>
  function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      eyeIcon.innerHTML = '<path fill-rule="evenodd" d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm-7.5-5a8.24 8.24 0 017.5-5 8.24 8.24 0 017.5 5 8.24 8.24 0 01-7.5 5 8.24 8.24 0 01-7.5-5z" clip-rule="evenodd"/>';
    } else {
      passwordInput.type = 'password';
      eyeIcon.innerHTML = '<path d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>';
    }
  }

  // Prevent zooming on input focus in mobile devices
  document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input[type="email"], input[type="password"], input[type="text"]');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.style.fontSize = '16px';
      });
    });
  });
</script>

@include('public.layouts.footer')
