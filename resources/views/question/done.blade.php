<x-layout>
    <div id="app" style="display: none">
        <div class="success-page">
            <div class="wrap" id="done-information">
                    <div class="page-title">
                        <img src="{{$assetUrl}}/art_success.svg" alt="success" class="art" />
                        <h2><em>Your question has been sent.</em></h2>
                    </div>
                    <div class="next-steps">
                        <h4>Next steps</h4>
                        <div class="step">
                            <img src="{{$assetUrl}}/icon-peoplesearch.svg" alt="People search icon"/>
                            <p>We will look for a person who can help you with your question.</p>
                        </div>
                        <div class="step">
                                <img src="{{$assetUrl}}/icon-sms.svg" alt="People search icon"/>
                            <p>When he/she has accepted it, you will receive an SMS from us and you will see the new appointment in your agenda.</p>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</x-layout>
<script>
    window.addEventListener("load", (event) => {
        new Vue({
            delimiters: ['${', '}'],
            el: '#app',
            data() {
            },
            mounted() {
                window.jQuery('#app').prop('style','');
            },
            computed: {
            },
        })
    });
</script>
