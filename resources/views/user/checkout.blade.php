<p>決済ページへリダイレクトします。</p>
<script src="https://js.stripe.com/v3/"></script> 
<script>
    // 公開鍵と秘密鍵を、コントローラから受け取る
    const publicKey = '{{ $publicKey }}'
    const stripe = Stripe(publicKey) 

    window.onload = function() {
        stripe.redirectToCheckout({
            sessionId: '{{ $session->id }}'
            }).then(function (result) {
                window.location.href = '{{ route('user.cart.cancel') }}';
                });
                    } 
</script>