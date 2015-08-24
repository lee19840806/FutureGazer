<div class="container">
    <div class="text-center">
        <?= $this->Flash->render() ?>
    </div>
    <div style="max-width: 300px; margin: 0 auto;">
        <form action="/Users/login" method="post">
            <h2>Sign In</h2>
            <br>
            <label for="inputEmail" class="sr-only">Email address</label>
            <input type="email" id="username" name="username" class="form-control" placeholder="Email address" required>
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            <br>
            <button class="btn btn-success btn-block" type="submit"><strong>Sign In</strong></button>
        </form>
        <br>
        <hr>
        <br>
        <p><strong>Sign up if you don't have an account</strong></p>
        <a class="btn btn-primary btn-block" href="/Users/register" role="button"><strong>Sign Up</strong></a>
    </div>
</div>