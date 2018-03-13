# Laravel Post Manager

[![Latest Stable Version](https://poser.pugx.org/niku-solutions/cms/v/stable)](https://packagist.org/packages/niku-solutions/cms)
[![Latest Unstable Version](https://poser.pugx.org/niku-solutions/cms/v/unstable)](https://packagist.org/packages/niku-solutions/cms)
[![License](https://poser.pugx.org/niku-solutions/cms/license)](https://packagist.org/packages/niku-solutions/cms)
[![Monthly Downloads](https://poser.pugx.org/niku-solutions/cms/d/monthly)](https://packagist.org/packages/niku-solutions/cms)

A API based codeable post manager for Laravel with custom fields. Extendable as you wish. Based on the API request, you will receive the post type configurations
in a way where you can build your front-end with. We will take care of the CRUD functionality with support of taxonomies, media management and post meta.

We use our package internally in our projects to remove the need of basic post management. We are now able to setup advanced dashboard functionality for all type of
post data like Pages, Posts, Products and whatever post type or category you require. You can add or remove custom fields in no time with no need to touch the
database as the package does that automatically for you and save the data and shows it to you when displaying the editting form.

> We are working on a decoupled front-end package in Vue.js and Axios which makes it possible to interact with the API in your Laravel project or Single Page Application.

#### Features
* Custom post types
* Configuration pages
* Taxonomies like categories
* Media manager with upload functionality and management
* Repeating custom field groups
* Custom fields
* Validation rules for custom fields
* Conditional custom fields based on template selection
* Easy default user authentication based on if a user is logged in
* Possibility to let users only view their own posts
* Menu management support, you will need our front-end package for that.

## Installation

Install the package via composer:

```
composer require niku-solutions/cms
```

Register the following class into the 'providers' array in your config/app.php

```php
Niku\Cms\CmsServiceProvider::class,
```

Register the following middleware to whitelist your post types and config groups in the route files. You dont have to do anything further with this as we use this in our provider to secure the api routes of the post manager.

```php
use Niku\Cms\Http\Middlewares\WhitelistPostTypesMiddleware;
use Niku\Cms\Http\Middlewares\WhitelistConfigGroupsMiddleware;

protected $routeMiddleware = [
	...
    'posttypes' => WhitelistPostTypesMiddleware::class,
    'groups' => WhitelistConfigGroupsMiddleware::class,
	...
];
```

You need to run the following artisan command to publish the required config file to register your post types.

```
php artisan vendor:publish --tag=niku-config
```

If you run the following vendor publish, you will receive a example set of post types to use

```
php artisan vendor:publish --tag=niku-posttypes
```

Migrate the database tables by running:

```
php artisan migrate
```

### Usage

Before you are able to use the post types, you need to whitelist and setup the required custom fields and templates in the config/niku-cms.php file.

```php
return [
    'post_types' => [

        // Default
        'attachment' => App\Cms\PostTypes\Attachment::class,

        // CMS
        'page' => App\Cms\PostTypes\Pages::class,
        'posts' => App\Cms\PostTypes\Pages::class,
        'posts-category' => App\Cms\PostTypes\PostsCategory::class,

    ],

    'config_types' => [

        // Registering the single config page
        'defaultsettings' => App\Cms\ConfigTypes\DefaultSettings::class,        

    ];
```

You can register the routes by pasting the following method in your route file. You can add middlewares like you would normally do to
secure the routes with authentication etc. The post_type in the registed routes are variable but secured by a parameter in the method, so by default no api requests are enabled.

To enable the API routes, you need to register the names of the post types you would like to use as you see in the 'register_post_types' array key below.
When registering a post type, you fill in the name of the array key in the config/niku-cms.php file. For more information about the config, read on.

If you for example have 2 user roles which have to communicate to the same post type but require different permissions, you can create 2 config files
where the normal user account can only view their own posts, and the superadmin can view all of the users their posts. You do that by naming the array
key of the config/niku-cms.php unique and creating 2 config files where the '$identifier' is pointed to the same 'post_type'.

```php
Niku\Cms\Cms::postTypeRoutes([
	'register_post_types' => [
		'posts',
		'superadminposts',
	],
]);

// Registering the routes for config pages
Niku\Cms\Cms::postTypeRoutes([
	'register_groups' => [
		'defaultsettings',		
	],
]);
```

For each post type registered, you can set up default data and custom fields. You can add validations to the validation array key of the custom field you insert. All Laravel validation rules
will be supported as it will only pass it thru to the validator class.

```php
namespace App\Cms\PostTypes;

use Niku\Cms\Http\NikuPosts;

class Pages extends NikuPosts
{
    // The label of the custom post type
    public $label = 'Pages';

    // Custom post type identifer
	public $identifier = 'page';

    // Users can only view their own posts when this is set to true
    public $userCanOnlySeeHisOwnPosts = false;    

    public $config = [

    ];

    // Setting up the template structure
    public $templates = [
        'default' => [
            'customFields' => [
                'post_content' => [
                    'component' => 'niku-cms-text-customfield',
                    'label' => 'Text',
                    'value' => '',
                    'validation' => 'required',
                ],
                'author' => [
                    'component' => 'niku-cms-text-customfield',
                    'label' => 'Author',
                    'validation' => 'required',
                ],
                // more custom fields
            ],
        ],
    ];

}

```

Do you want to change the custom fields displayed based on the template? You can add multiple views which are selectable in the frontend for the end user and change the visible custom fields.

```php
public $templates = [
    'default' => [
        'label' => 'Default page',
        'template' => 'default',
        'customFields' => [
            'text' => [
                'component' => 'niku-cms-text-customfield',
                'label' => 'Text',
                'value' => '',
                'validation' => 'required',
            ]
        ]
    ],
    'sidebar' => [
        'label' => 'Sidebar layout',
        'template' => 'sidebar-layout',
        'customFields' => [
            'text' => [
                'component' => 'niku-cms-text-customfield',
                'label' => 'Text',
                'value' => '',
                'validation' => 'required',
            ]
        ]
    ],
];
```

#### Blog

If you want a blog like method, you can do the following.

Enable the following type in your routes/web.php.

```php
Route::get('blog', 'BlogController@blog');
Route::get('blog/{slug}', 'BlogController@singleBlog');
```

Next you enable the required methods in the controller.

```php
public function blog()
{
    $posts = Posts::where([
        ['status', '=', '1'],
        ['post_type', '=', 'post']
    ])->with('postmeta')->get();
    return view('static.blog', compact('posts'));
}
```

And then in your view, you do the following. This syntax will be recreated in the future to make it more fluent but for now it works.

```blade
@foreach($posts as $post)
    <div class="row">
        @if(!empty($post->getMeta('image')))
            <?php
            $image = json_decode($post->getMeta('image'));
            $image = $image->url;
            ?>
            <div class="col-md-3">
                <img src="{{ $image }}" class="img-responsive">
            </div>
        @endif
        <div class="col-md-8">
            <h2>{{ $post->post_title }}</h2>
            <p>{!! $post->getMeta('excerpt') !!}</p>
            <br/>
            <a class="btn btn-default" href="/blog/{{ $post->post_name }}">Read more</a>
        </div>
    </div>
@endforeach
```

#### Switching templates

If you have enabled more than 1 post type template in the config/niku-cms.php, you will see a option appear in the backend to switch between templates. When you have
selected one template, you can switch views in the frontend like this.

```blade
@extends('static.layouts.' . $posts->template)
```

## API

To retrieve the base structure of your post type, you can request the following post API where the value is 0. This means we creating a new post.
The result of this request will give you the structure of what you have inserted in the config file. With this data you can build the front-end
of your page to automaticly create the input fields of the create form.

You will trigger this API on initialisation of the page where you want to create a new post item. (/superadmin/pages/create).

```
POST /your-prefix/{post_type}/show/0
```

```json
{
  "post": {
    "template": "default"
  },
  "postmeta": [],
  "templates": {
    "default": {
      "customFields": {
        "text": {
          "component": "niku-cms-text-customfield",
          "label": "Text",
          "value": "",
          "validation": "required",
          "id": "text"
        },
        "PostMultiselect": {
          "component": "niku-cms-posttype-multiselect",
          "label": "Post multiselect",
          "post_type": [
            "page"
          ],
          "validation": "required",
          "id": "PostMultiselect"
        },
        "periods": {
          "component": "niku-cms-repeater-customfield",
          "label": "Perioden",
          "validation": "required",
          "customFields": {
            "label": {
              "component": "niku-cms-text-customfield",
              "label": "Label",
              "value": "",
              "validation": ""
            },
            "boolean": {
              "component": "niku-cms-boolean-customfield",
              "label": "Boolean button",
              "value": "",
              "validation": ""
            }
          },
          "id": "periods"
        }
      }
    }
  },
  "config": []
}
```

## Extending the custom fields and defining your own

You can create your own custom fields by using the registered component identifier to identify which Vue component you need to show. This information
will be attached to the API request when requesting the following API;

```php
'text' => [
    'component' => 'niku-cms-text-customfield',
    'label' => 'Text',
    'value' => '',
    'validation' => 'required',
],
```

Registrate your component with they key you define in the post type config.

```javascript
Vue.component('niku-cms-text-customfield', require('./components/customFields/text.vue'));
```

And for example use the following code structure

```vue
<template>
    <div class="form-group">
        <label for="post_name" class="col-sm-3 control-label">{{ data.label }}:</label>
        <div class="col-sm-9">
            <input type="text" name="{{ data.id }}" v-model="input" class="form-control" value="{{ data.value }}">
        </div>
    </div>
</template>
<script>
export default {
    data () {
        return {
            'input': '',
        }
    },
    props: {
        'data': ''
    },
    ready () {
    }
}
</script>
```

## Security Vulnerabilities

If you find any security vulnerabilities, please send a direct e-mail to Nick Kuijpers at n.kuijpers@niku-solutions.nl.

## License

The MIT License (MIT). Please see [MIT license](http://opensource.org/licenses/MIT) for more information.
