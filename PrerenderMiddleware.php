<?php
class PrerenderMiddleware extends \Slim\Middleware
{
  protected $backendURL;
  protected $token;

  public function __construct($backendURL, $token){
      $this->backendURL = $backendURL;
      $this->token = $token;
  }

  public function isBot(){

    // Google and other engines using this
    if (isset($_GET['_escaped_fragment_'])){
      return true;
    }

    $agent = $this->app->request->getUserAgent();
    // regex with our bot list
    $bots = "!(Googlebot|bingbot|Googlebot-Mobile|Yahoo|YahooSeeker|FacebookExternalHit|Twitterbot|TweetmemeBot|BingPreview|developers.google.com/\+/web/snippet/)!i";

    // if anything in our search string is in the user agent
    if (preg_match($bots, $agent)){
      return true;
    }

    // not a bot
    return false;
  }

  public function isIgnoredExtension(){
    $resourceURI = $this->app->request->getResourceUri();
    $extensions = "!(\.js|\.css|\.xml|\.less|\.png|\.jpg|\.jpeg|\.gif|\.svg|\.pdf|\.doc|\.txt|\.ico|\.rss|\.zip|\.mp3|\.rar|\.exe|\.wmv|\.doc|\.avi|\.ppt|\.mpg|\.mpeg|\.tif|\.wav|\.mov|\.psd|\.ai|\.xls|\.mp4|\.m4a|\.swf|\.dat|\.dmg|\.iso|\.flv|\.m4v|\.torrent)!i";
    if (preg_match($extensions, $resourceURI)){
      return true;
    } else {
      return false;
    }
  }

  public function shouldPreRender(){
    // return false if not a bot
    if (!$this->isBot()){
      return false;
    }

    // don't preRender if ignored extension
    if ($this->isIgnoredExtension()){
      return false;
    }

    return true;
  }

  public function preRender(){

    $env = $this->app->environment;
    $resourceURI = $this->app->request->getResourceUri();
    $agent = $this->app->request->getUserAgent();

    // Our request string
    $url = $this->backendURL . $env['slim.url_scheme'] . '://' . $env['HTTP_HOST'] . $resourceURI;

    // Our token
    $xtoken = 'X-Prerender-Token: ' . $this->token;

    // Init cURL
    $ch = curl_init($url);

    // Send our token
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($xtoken));

    // Set user agent
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);

    // Don't need header
    curl_setopt($ch, CURLOPT_HEADER, 0);

    // Hold onto return
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result

    // Fetch and return content, set into body
    $prerender = curl_exec($ch);
    curl_close($ch);
    $this->app->response->setBody($prerender);
  }

  public function call(){

      if ($this->shouldPreRender()){
        $this->preRender();
      } else {
        $this->next->call(); // render as usual
      }
  }
}
?>
