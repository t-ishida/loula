# Loula

yet another PHP Http Client(paralel request(async)).

## How To Use

### case1 - leave throw API

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
class Printer implements \Loula\Observable
{
    public function at(\Loula\HttpResponse $response) {
        var_dump($response->getBody());
    }
}
$obj = new \Loula\HttpMultiClient();
$obj->addRequest(new \Loula\HttpRequest('POST', 'http://dummy.api.dummy/accept1', array('apiKey' => 'key'), null, null, array(new Printer())));
$obj->addRequest(new \Loula\HttpRequest('POST', 'http://dummy.api.dummy/accept2', array('apiKey' => 'key'), null, null, array(new Printer())));
$obj->addRequest(new \Loula\HttpRequest('POST', 'http://dummy.api.dummy/accept3', array('apiKey' => 'key'), null, null, array(new Printer())));
$obj->addRequest(new \Loula\HttpRequest('POST', 'http://dummy.api.dummy/accept4', array('apiKey' => 'key'), null, null, array(new Printer())));
$obj->run();

print "end\n";
print (microtime() - $before) . "\n";
// print response body after "end"
```

### case2 - request many api after rendering html

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
class Reciever implements \Loula\Observable
{
    private $response = null;
    public function at(\Loula\HttpResponse $response) {
        $this->response = $response;
    }

    public function getContent ()
    {
        return json_decode($this->response->getBody());
    }
}


$obj = new \Loula\HttpMultiClient();
$profileReciever  = new Reciever();
$userReciever  = new Reciever();
$newsReciever  = new Reciever();
$obj->addRequest(new \Loula\HttpRequest('GET', 'http://dummy.api.dummy/profile1', null, null, null, array($profileReciever)));
$obj->addRequest(new \Loula\HttpRequest('GET', 'http://users.api.dummy/userList', null, null, null, array($userReciever)));
$obj->addRequest(new \Loula\HttpRequest('GET', 'http://news.api.dummy/news-list', null, null, null, array($newsReciever)));
$obj->run();
?>
<html>
<body>
  <div>
    <p id="profile">wait a moment.</p>
    <ul id="news" />
    <ul id="users" />
  </div>
</body>
</html>
<?php $obj->process()?>
<script type="text/javascript">
(function(d, $){
    setTimeout(function() {
        var profile = <?php echo json_encode($profileReciever->getContent())?>;
        var users   = <?php echo json_encode($userReciever->getContent())?>;
        var news    = <?php echo json_encode($newsReciever->getContent())?>;
        $('profile').appendChild(d.createTextNode(profilie.data.name));
        for (var i = 0, l = users.data.list.length; i < l; i++) {
            var li = d.createElement('li');
            li.appendChild(d.createTextNode(users.data.list[i].name));
            $('users').appendChild(li);
        }
        for (var i = 0, l = news.data.list.length; i < l; i++) {
            var li = d.createElement('li');
            li.appendChild(d.createTextNode(news.data.list[i].name));
            $('news').appendChild(li);
        }
    }, 0);
})(document, documtn.getElementById);
</script>
```
## License

This library is available under the MIT license. See the LICENSE file for more info.

