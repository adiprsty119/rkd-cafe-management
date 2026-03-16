<form id="resetForm">

    <input type="hidden" id="token" value="<?= $_GET['token'] ?>">

    <input type="password" id="password">

    <input type="password" id="confirm">

    <button type="submit">Reset Password</button>

</form>

<script>
    document.getElementById("resetForm").onsubmit = async e => {

        e.preventDefault()

        let token = document.getElementById("token").value
        let password = document.getElementById("password").value
        let confirm = document.getElementById("confirm").value

        let res = await fetch("/rkd-cafe/app/controllers/AuthController.php?action=resetPassword", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                reset_token: token,
                password: password,
                confirm_password: confirm
            })

        })

        let data = await res.json()

        alert(data.message || data.error)

    }
</script>