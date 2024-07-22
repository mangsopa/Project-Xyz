<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('verify.store') }}">
        @csrf
        <div>
            <x-input-label for="code" :value="__('code')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" required />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Did not receive the code?') }}
        <button id="resend-button" class="text-blue-500" disabled>{{ __('Resend Code') }}</button>
        <span id="countdown" class="ml-2"></span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countdownElement = document.getElementById('countdown');
            const resendButton = document.getElementById('resend-button');
            let timeLeft = 60; // waktu mundur dalam detik

            function updateCountdown() {
                if (timeLeft <= 0) {
                    resendButton.disabled = false;
                    countdownElement.textContent = '';
                } else {
                    resendButton.disabled = true;
                    countdownElement.textContent = `(${timeLeft}s)`;
                    timeLeft--;
                }
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);

            resendButton.addEventListener('click', function() {
                // Logic untuk mengirim ulang kode OTP
                fetch('{{ route('otp.resend') }}', {
                        method: 'POST',
                        // body: JSON.stringify({
                        //     user_id: '{{ auth()->user()->id }}',
                        //     user_code: '{{ auth()->user()->code }}'
                        // }),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }

                    })
                    .then(response => response.json())
                    .then(data => {
                        // Reset waktu mundur setelah mengirim ulang kode
                        timeLeft = 60;
                        updateCountdown();
                    });
                // .catch(error => {
                //     console.error('Error:', error);
                // });
            });
        });
    </script>
</x-guest-layout>
Z
