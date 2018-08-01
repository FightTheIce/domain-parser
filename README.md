# domain-parser
PHP Domain Name Parser

```php
$url = 'http://username:password@subdomain.hostname.com:9090/path?arg=value#anchor';

$parser = new FightTheIce\Domain\Parser();
$parser->parse($url);

echo $parser->getSubdomain() . PHP_EOL;
echo $parser->getDomain() . PHP_EOL;
echo $parser->getGld() . PHP_EOL;

echo $parser->getUrl() . PHP_EOL;
```
