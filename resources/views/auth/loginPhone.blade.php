<x-layout>
    <section class="signup-form">
        <div class="wrap">
            <div class="card signup">
                <h3>Inloggen</h3>
                <form method="post" onSubmit="document.getElementById('submitPhoneButton').disabled=true;" action="/login">
                    @csrf
                    <div class="input-group">
                        <div class="with-lt-icon">
                            <input type="text" id="PhoneNumber" name="PhoneNumber" placeholder="Telefoonnummer" oninput="checkLength();" />
                            <i>phone_android</i>
                        </div>
                    </div>
                    @if(! empty($error))
                        <div class="error-message">{{ $error }}</div>
                    @endif
                    <div class="buttons-group">
                        <button type="submit" class="button disabled" id="submitPhoneButton">Inloggen Care portal</button>
                    </div>
                </form>
                <div class="footer">
                    If you click on 'Login Care portal', you will receive an SMS with the login code. You can then fill this in to continue.                </div>
            </div>
        </div>
    </section>
    <section class="signup-information">
        <div class="wrap">
            <h3>What is Care portal?</h3>
            <p>Will you become a client of Care portal? Then you can keep control of the care you need with our online care app Care portal. You can do this, for example, by scheduling an appointment yourself via the app. You can easily and quickly engage a healthcare professional, but you can also use the app to ask your friends, family and network for support. </p>
            <p>
                You can only use the app if you are a client of ours. You can register for Care portal support via the registration form on
            </p>
        </div>
        <h3>Have you already registered for Care portal?</h3>
        <p>Has your supervisor already registered you for the Care portal? Then you can easily log in here with your phone number. Not registered yet? Ask your supervisor about the possibilities. </p>
        <h3> For which teams is Care portal already active? </h3>
        <ul>
            <li>Test team 1</li>
            <li>Test team 2</li>
            <li>Test team 3</li>
            <li>Test team 4</li>
        </ul>
        <p class="expansion">Soon more teams will be actively using the Care portal. We'll keep you posted!</p>
        <div class="buttons-group">
            <div class="with-lt-icon">
                <i>arrow_back</i>
            </div>

            <div class="with-lt-icon">
                <a href="/login/" class="button alt">Login</a>
                <i>arrow_upward</i>
            </div>
        </div>
    </section>
</x-layout>
    <script>
        function checkLength() {
            var value = document.getElementById("PhoneNumber").value;

            if (value.length >= 10) {
                document.getElementById("submitPhoneButton").classList.remove("disabled");
            } else {
                document.getElementById("submitPhoneButton").classList.add("disabled");
            }
        }
    </script>
