# Hyperf 多语言模型组件

该组件为数据库模型提供实现多语言的能力。

ps: 组件从 `hyperf-ext/translatable` fork而来，原组件只支持到hyperf2.2版本。作者目前长时间未更新。

## 安装

```shell script
composer require xmo/mine-translatable
```

## 发布配置

```shell script
php bin/hyperf.php vendor:publish xmo/mine-translatable
```

> 文件位于 `config/autoload/translatable.php`。

> 注意，该组件依赖 `hyperf/translation`，不要忘记也要发布其配置并按需设置。

## 配置

```php
[
    /*
    |--------------------------------------------------------------------------
    | 应用语言列表
    |--------------------------------------------------------------------------
    |
    | 包含应用所有可用语言的数组。
    |
    */
    'locales' => [
        'en',
        'fr',
        'zh' => [
            'CN',
            'TW',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 语言分隔符
    |--------------------------------------------------------------------------
    |
    | 用于在定义可用语言时连接语言和国家（或地区）之间的字符串。例如，如果设置为 `_`，
    | 则“中文（中国）”的语言会以 `zh_CN` 的形式存储到数据库中。
    |
    */
    'locale_separator' => '_',

    /*
    |--------------------------------------------------------------------------
    | 默认语言
    |--------------------------------------------------------------------------
    |
    | 指定要使用的默认语言。该组件默认使用 `hyperf/translation` 组件的语言设置。
    | 如果出于某种原因想要覆盖此默认设置，则可以在此处设置要使用的默认值。
    | 如果在此处设置一个值，它将仅使用当前的值，而不会回退到 `hyperf/translation`
    | 组件的设置。
    |
    */
    'locale' => null,

    /*
    |--------------------------------------------------------------------------
    | 使用回退
    |--------------------------------------------------------------------------
    |
    | 设置是否启用回退语言。为了增加灵活性，定义多语言模型的 $useTranslationFallback
    | 属性可覆盖此处的设置。
    |
    */
    'use_fallback' => false,

    /*
    |--------------------------------------------------------------------------
    | 使用属性回退
    |--------------------------------------------------------------------------
    |
    | 如果所选语言的属性为空，则属性回退特性将返回回退语言的翻译后的值。
    | 注意，必须启用 `use_fallback`。
    |
     */
    'use_property_fallback' => true,

    /*
    |--------------------------------------------------------------------------
    | 回退语言
    |--------------------------------------------------------------------------
    |
    | 回退语言是当请求的翻译不存在时，返回设置的回退语言的翻译。
    | 如要禁用，请设置为 `false`。如果设置为 `null`，将会遍历语言列表中配置的所有语言，
    | 直到找到第一个有效的翻译，否则会遍历完整个列表。
    | 语言列表从上到下遍历，对于基于国家（或地区）的语言，会先检查简单的语言代码。
    | 因此，例如在检查 `zh_CN` 之前会先检查 `zh`。
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Translation 模型命名空间
    |--------------------------------------------------------------------------
    |
    | 定义默认的 'Translation` 类命名空间。
    | 例如，如果要使用 `App\Translations\CountryTranslation` 而不是 `App\CountryTranslation`，
    | 请设置为 `App\Translations`。
    |
    */
    'translation_model_namespace' => null,

    /*
    |--------------------------------------------------------------------------
    | Translation 模型后缀
    |--------------------------------------------------------------------------
    |
    | 定义默认的 `Translation` 类名后缀。
    | 例如，如果要使用 `CountryTrans` 而不是 `CountryTranslation`，请设置为 `Trans`。
    |
    */
    'translation_suffix' => 'Translation',

    /*
    |--------------------------------------------------------------------------
    | 语言字段名
    |--------------------------------------------------------------------------
    |
    | 定义 `Translation` 模型的 'locale' 字段名。
    |
    */
    'locale_key' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | 转换为数组时始终加载 Translation
    |--------------------------------------------------------------------------
    |
    | 将其设置为 `false` 可以提升性能，但是在使用 `toArray()` 时不会返回翻译，
    | 除非已经加载了关联的 `Translation` 模型。
    |
     */
    'to_array_always_loads_translations' => true,

    /*
    |--------------------------------------------------------------------------
    | 配置 RuleFactory 默认行为
    |--------------------------------------------------------------------------
    |
    | 用于控制 RuleFactory 行为的默认值。
    | 此处你可以为整个应用设置自己的默认格式和定界符。
    |
     */
    'rule_factory' => [
        'format' => \Mine\Translatable\Validation\RuleFactory::FORMAT_ARRAY,
        'prefix' => '%',
        'suffix' => '%',
    ],
];
```

## 使用示例

### 迁移

create_posts_table.php
```php
use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function(Blueprint $table) {
            $table->increments('id');
            $table->string('author');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}
```

create_post_translations_table
```php
use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreatePostTranslationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('post_translations', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id')->unsigned();
            $table->string('locale')->index();
            $table->string('title');
            $table->text('content');
        
            $table->unique(['post_id', 'locale']);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_translations');
    }
}
```

### 模型

Post.php
```php
use Hyperf\Database\Model\Model;
use Mine\Translatable\Contracts\TranslatableInterface;
use Mine\Translatable\Translatable;

class Post extends Model implements TranslatableInterface
{
    use Translatable;
    
    // 转换为数组时是否始终加载 Translation。默认为 `null`。
    // 值为 `null` 时使用配置文件的 `to_array_always_loads_translations` 值。
    protected static $autoloadTranslations = null;

    // 删除记录的同时删除关联的翻译。默认为 `false`。
    protected static $deleteTranslationsCascade = false;

    // 配置该属性会覆盖配置文件的 `use_fallback` 值。
    //protected $useTranslationFallback = false;

    // 配置对应的 Translation 模型的多语言字段列表
    public $translatedAttributes = ['title', 'content'];
    
    protected $fillable = ['author'];
}
```

PostTranslation.php
```php
use Hyperf\Database\Model\Model;

class PostTranslation extends Model
{
    public $timestamps = false;
    
    // 多语言字段列表
    protected $fillable = ['title', 'content'];
}
```

### 获取已翻译的属性

```php
use App\Post;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Context\ApplicationContext;

$post = Post::query()->first();
echo $post->translate('en')->title; // My first post

$translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);

$translator->setLocale('en');
echo $post->title; // My first post

$translator->ssetLocale('de');
echo $post->title; // Mein erster Post
```

### 存储已翻译的属性
```php
use App\Post;

$post = Post::query()->first();
echo $post->translate('en')->title; // My first post

$post->translate('en')->title = 'My cool post';
$post->save();

$post = Post::query()->first();
echo $post->translate('en')->title; // My cool post
```

### 填充翻译

```php
use App\Post;

$data = [
  'author' => 'Gummibeer',

  'en' => ['title' => 'My first post'],
  'fr' => ['title' => 'Mon premier post'],
];
$post = new Post();
$post->fill($data);
$post->save();

echo $post->translate('fr')->title; // Mon premier post
```