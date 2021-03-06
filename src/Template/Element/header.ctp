<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/"><img style="margin-top: -8px; width: 36px; height: 36px;" alt="Brand" src="/favicon.ico"></a>
            <a class="navbar-brand" href="/">Future Gazer</a>
        </div>
        
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="/Users/welcome">Home</a></li>
                <li><a href="#">About</a></li>
            </ul>
            
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <?= is_null($this->request->session()->read('Users.username')) ? 
                        $this->Html->link('Sign in', '/Users/login') :
                        $this->Html->link($this->request->session()->read('Users.username') . ', sign out', '/Users/logout');
                    ?>
                </li>
            </ul>
        </div>
    </div>
</nav>