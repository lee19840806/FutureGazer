<div class="container">
    <div class="text-center">
        <?= $this->Flash->render() ?>
    </div>
    <form action="/Users/login" method="post" style="max-width: 300px; margin: 0 auto;">
        <h2>Sign In</h2>
        <br>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" id="username" name="username" class="form-control" placeholder="Email address" required>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
        <br>
        <button class="btn btn-lg btn-success btn-block" type="submit">Sign In</button>
    </form>
</div>