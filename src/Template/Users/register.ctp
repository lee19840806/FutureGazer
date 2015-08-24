<div class="container">
    <div class="text-center">
        <?= $this->Flash->render() ?>
    </div>
    <div style="max-width: 300px; margin: 0 auto;">
        <form action="/Users/register" method="post">
            <h2>Sign Up</h2>
            <br>
            <label for="inputEmail" class="sr-only">Email address</label>
            <input type="email" id="username" name="username" class="form-control" placeholder="Email address" value="<?= isset($username) ? h($username) : ''; ?>" required>
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            <label for="inputPassword" class="sr-only">Confirm password</label>
            <input type="password" id="passwordConfirm" name="passwordConfirm" class="form-control" placeholder="Confirm password" required>
            <br>
            <button class="btn btn-primary btn-block" type="submit"><strong>Sign Up</strong></button>
        </form>
        <br>
        <hr>
        <br>
        <p><strong>Sign in if you already have an account</strong></p>
        <a class="btn btn-success btn-block" href="/Users/login" role="button"><strong>Sign In</strong></a>
    </div>
</div>