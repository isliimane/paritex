<?php

namespace App\Traits;

use App\Http\Resources\SiteResource\BlogResource;
use App\Http\Resources\SiteResource\BrandResource;
use App\Http\Resources\SiteResource\CampaignResource;
use App\Http\Resources\SiteResource\ProductResource;
use App\Http\Resources\SiteResource\ShopResource;
use App\Http\Resources\SiteResource\TopCategoryResource;
use App\Http\Resources\SiteResource\VideoResource;

trait HomePage
{
    public function parseSettingsData($media, $category, $brand, $campaign,$page): ?array
    {
        $skip = 0;

        $content_count = $page * 3;

        if ($page > 1)
        {
            $skip = ($page-1) * 3;
        }
        $settings = settingHelper('home_page_contents');
        $component_names = [];
        $results = $keys = [];
        if ($settings) {
            foreach ($settings as $key => $setting) {
                if (($key > $skip || $page == 1) && $key <= $content_count)
                {
                    foreach ($setting as $set_key => $item) {
                        if ($set_key == 'banner') {
                            $banners = [];
                            $component_names[]=$set_key;
                            $total = count($item['thumbnail']);
                            switch ($total):
                                case(4):
                                    $image_0 = $media->get($item['thumbnail'][0]);
                                    $image_1 = $media->get($item['thumbnail'][1]);
                                    $image_2 = $media->get($item['thumbnail'][2]);
                                    $image_3 = $media->get($item['thumbnail'][3]);
                                    $thumb = [
                                        @is_file_exists($image_0->image_variants['image_300x170'], $image_0->image_variants['storage']) ? @get_media($image_0->image_variants['image_300x170'], $image_0->image_variants['storage']) . '_0' : static_asset('images/default/default-image-300x170.png') . '_0' => $item['url'][0],
                                        @is_file_exists($image_1->image_variants['image_300x170'], $image_1->image_variants['storage']) ? @get_media($image_1->image_variants['image_300x170'], $image_1->image_variants['storage']) . '_1' : static_asset('images/default/default-image-300x170.png') . '_1' => $item['url'][1],
                                        @is_file_exists($image_2->image_variants['image_300x170'], $image_2->image_variants['storage']) ? @get_media($image_2->image_variants['image_300x170'], $image_2->image_variants['storage']) . '_2' : static_asset('images/default/default-image-300x170.png') . '_2' => $item['url'][2],
                                        @is_file_exists($image_3->image_variants['image_300x170'], $image_3->image_variants['storage']) ? @get_media($image_3->image_variants['image_300x170'], $image_3->image_variants['storage']) . '_3' : static_asset('images/default/default-image-300x170.png') . '_3' => $item['url'][3],
                                    ];
                                    $banners[] = $thumb;
                                    break;
                                case(3):
                                    $image_0 = $media->get($item['thumbnail'][0]);
                                    $image_1 = $media->get($item['thumbnail'][1]);
                                    $image_2 = $media->get($item['thumbnail'][2]);
                                    $thumb = [
                                        @is_file_exists($image_0->image_variants['image_400x235'], $image_0->image_variants['storage']) ? @get_media($image_0->image_variants['image_400x235'], $image_0->image_variants['storage']) . '_0' : static_asset('images/default/default-image-400x235.png') . '_0' => $item['url'][0],
                                        @is_file_exists($image_1->image_variants['image_400x235'], $image_1->image_variants['storage']) ? @get_media($image_1->image_variants['image_400x235'], $image_1->image_variants['storage']) . '_1' : static_asset('images/default/default-image-400x235.png') . '_1' => $item['url'][1],
                                        @is_file_exists($image_2->image_variants['image_400x235'], $image_2->image_variants['storage']) ? @get_media($image_2->image_variants['image_400x235'], $image_2->image_variants['storage']) . '_2' : static_asset('images/default/default-image-400x235.png') . '_2' => $item['url'][2],
                                    ];
                                    $banners[] = $thumb;
                                    break;
                                case(2):
                                    $image_0 = $media->get($item['thumbnail'][0]);
                                    $image_1 = $media->get($item['thumbnail'][1]);
                                    $thumb = [
                                        @is_file_exists($image_0->image_variants['image_620x320'], $image_0->image_variants['storage']) ? @get_media($image_0->image_variants['image_620x320'], $image_0->image_variants['storage']) . '_0' : static_asset('images/default/default-image-620x320.png') . '_0' => $item['url'][0],
                                        @is_file_exists($image_1->image_variants['image_620x320'], $image_1->image_variants['storage']) ? @get_media($image_1->image_variants['image_620x320'], $image_1->image_variants['storage']) . '_1' : static_asset('images/default/default-image-620x320.png') . '_1' => $item['url'][1],
                                    ];
                                    $banners[] = $thumb;
                                    break;
                                case(1):
                                    $image_0 = $media->get($item['thumbnail'][0]);
                                    $thumb = [
                                        @is_file_exists($image_0->image_variants['image_1260x452'], $image_0->image_variants['storage']) ? @get_media($image_0->image_variants['image_1260x452'], $image_0->image_variants['storage']) . '_0' : static_asset('images/default/default-image-1280x420.png') . '_0' => $item['url'][0],
                                    ];
                                    $banners[] = $thumb;
                                    break;
                            endswitch;
                            $results = $this->keyDefine('banners', $key, $banners, $results);
                            $keys[] = 'banners';
                        }
                        if ($set_key == 'campaign') {
                            $component_names[]=$set_key;
                            $campaigns = CampaignResource::collection($campaign->campaignByIds($item));
                            $results = $this->keyDefine('campaigns', $key, $campaigns, $results);
                            $keys[] = 'campaigns';
                        }
                        if ($set_key == 'popular_category') {
                            $component_names[]=$set_key;
                            $popular_category = TopCategoryResource::collection($category->categoryByIds($item));
                            $results = $this->keyDefine('popular_categories', $key, $popular_category, $results);
                            $keys[] = 'popular_categories';
                        }
                        if ($set_key == 'top_category') {
                            $component_names[]=$set_key;
                            $top_categories = TopCategoryResource::collection($category->categoryByIds($item, ''));
                            $results = $this->keyDefine('top_categories', $key, $top_categories, $results);
                            $keys[] = 'top_categories';
                        }
                        if ($set_key == 'todays_deal') {
                            $component_names[]=$set_key;
                            if (in_array('today_deals', $keys)) {
                                $position = array_search('today_deals', array_values($keys));
                                $todays_deal = $results['today_deals-' . $position];
                            } else {
                                $todays_deal = ProductResource::collection($this->product->todayDeals());
                            }

                            $results = $this->keyDefine('today_deals', $key, $todays_deal, $results);
                            $keys[] = 'today_deals';
                        }
                        if ($set_key == 'custom_products') {
                            $component_names[]=$set_key;
                            $products = ProductResource::collection($this->product->productByIds($item));
                            $results = $this->keyDefine('custom_products', $key, $products, $results);
                            $keys[] = 'custom_products';
                        }
                        if ($set_key == 'flash_deal') {
                            $component_names[]=$set_key;
                            if (in_array('flash_products', $keys)) {
                                $position = array_search('flash_products', array_values($keys));
                                $flash_products = $results['flash_products-' . $position];
                            } else {
                                $flash_products = ProductResource::collection($this->product->campaignProducts());
                            }

                            $results = $this->keyDefine('flash_products', $key, $flash_products, $results);
                            $keys[] = 'flash_products';
                        }
                        if ($set_key == 'latest_product') {
                            $component_names[]=$set_key;
                            if (in_array('latest_products', $keys)) {
                                $position = array_search('latest_products', array_values($keys));
                                $latest_products = $results['latest_products-' . $position];
                            } else {
                                $latest_products = ProductResource::collection($this->product->latestProducts());
                            }

                            $results = $this->keyDefine('latest_products', $key, $latest_products, $results);
                            $keys[] = 'latest_products';
                        }
                        if ($set_key == 'category_section') {
                            $component_names[]=$set_key;

                            $image = $media->get($item['banner']);
                            $category_sec_banner = $image && is_file_exists(@$image->image_variants['image_405x745'], @$image->image_variants['storage']) ? @get_media(@$image->image_variants['image_405x745'], $image->image_variants['storage']) : static_asset('images/default/default-image-405x745.png');
                            $category_sec_banner_url = $item['banner_url'];
                            $category_products = $category->categoryProducts($item['category']);
                            $category_get = $category->get($item['category']);
                            $category_products['name'] = $category_get ? $category_get->getTranslation('title',languageCheck()) : '';
                            $results = $this->keyDefine('category_sec_banner', $key, $category_sec_banner, $results);
                            $results = $this->keyDefine('category_sec_banner_url', $key, $category_sec_banner_url, $results);
                            $results = $this->keyDefine('gadget_product', $key, $category_products, $results);
                            $keys[] = 'gadget_product';

                        }
                        if ($set_key == 'best_selling_products') {
                            $component_names[]=$set_key;
                            if (in_array('best_selling_product', $keys)) {
                                $position = array_search('best_selling_product', array_values($keys));
                                $best_selling_products = $results['best_selling_product-' . $position];
                            } else {
                                $best_selling_products = ProductResource::collection($this->product->bestSelling());
                            }

                            $results = $this->keyDefine('best_selling_product', $key, $best_selling_products, $results);
                            $keys[] = 'best_selling_product';
                        }
                        if ($set_key == 'offer_ending_soon') {
                            $component_names[]=$set_key;
                            $image = @$media->get($item['banner']);
                            $offer_end_sec_banner = $image && is_file_exists(@$image->image_variants['image_405x745'], @$image->image_variants['storage']) ? @get_media($image->image_variants['image_405x745'], $image->image_variants['storage']) : static_asset('images/default/default-image-405x745.png');
                            $offer_end_sec_banner_url = @$item['banner_url'];
                            $offer_end = ProductResource::collection($this->product->offerEndingSoon());
                            $results = $this->keyDefine('offer_ending', $key, $offer_end, $results);
                            $results = $this->keyDefine('offer_ending_banner', $key, $offer_end_sec_banner, $results);
                            $results = $this->keyDefine('offer_ending_banner_url', $key, $offer_end_sec_banner_url, $results);
                            $keys[] = 'offer_ending';
                        }
                        if ($set_key == 'latest_news') {
                            $component_names[]=$set_key;
                            if (in_array('blog', $keys)) {
                                $position = array_search('blog', array_values($keys));
                                $blogs = $results['blog-' . $position];
                            } else {
                                $blogs = BlogResource::collection($this->blog->homePageBlogs());
                            }
                            $results = $this->keyDefine('blog', $key, $blogs, $results);
                            $keys[] = 'blog';
                        }
                        if ($set_key == 'popular_brands') {
                            $component_names[]=$set_key;
                            if (in_array('brands', $keys)) {
                                $position = array_search('brands', array_values($keys));
                                $brands = $results['brands-' . $position];
                            } else {
                                $brands = BrandResource::collection($brand->homePageBrands($item));
                            }

                            $results = $this->keyDefine('brands', $key, $brands, $results);
                            $keys[] = 'brands';
                        }
                        if ($set_key == 'download_section') {
                            $component_names[]=$set_key;
                            $image = $media->get($item['banner']);
                            if ($image)
                                $download_section['banner'] = is_file_exists($image->image_variants['image_320x320'], $image->image_variants['storage']) ? @get_media($image->image_variants['image_320x320'], $image->image_variants['storage']) : static_asset('images/default/default-image-320x320.png');
                            else {
                                $download_section['banner'] = static_asset('images/default/default-image-320x320.png');
                            }
                            $download_section['text'] = $item['text'];
                            $download_section['sub_text'] = $item['sub_text'];
                            $download_section['apple_store'] = settingHelper('apple_store_link');
                            $download_section['play_store'] = settingHelper('play_store_link');

                            $results = $this->keyDefine('download_section', $key, $download_section, $results);
                            $keys[] = 'download_section';
                        }
                    }
                }
            }
        }

        return [
            'components'        => $results,
            'component_names'   => $component_names,
        ];
    }

    protected function keyDefine($key, $index, $data, $results)
    {
        if ($key == 'banners') {
            $data = count($data) > 0 ? array_merge(...$data) : [];
        }


        $results[$key . '-' . $index] = $data;

        return $results;
    }

    protected function cartList($carts): array
    {
        $cart_list = [];

        foreach ($carts as $cart) {
            if ($cart->product)
            {
                $sku_product = $cart->product->stock->where('name', $cart->variant)->first();
                $cart_list[] = [
                    'id'                    => $cart->id,
                    'seller_id'             => $cart->seller_id,
                    'product_name'          => $cart->product->product_name,
                    'product_slug'          => $cart->product->slug,
                    'image_72x72'           => $cart->image_72x72,
                    'image_40x40'           => $cart->image_40x40,
                    'sku'                   => $sku_product ? $sku_product->sku : '',
                    'discount'              => $cart->retail_discount > 0 ? $cart->retail_discount : $cart->discount,
                    'price'                 => $cart->retail_price > 0 ? $cart->retail_price : $cart->price,
                    'quantity'              => $cart->quantity,
                    'trx_id'                => $cart->trx_id,
                    'shipping_cost'         => $cart->shipping_cost,
                    'tax'                   => $cart->retail_tax > 0 ? $cart->retail_tax : $cart->tax,
                    'current_stock'         => $cart->current_stock,
                    'min_quantity'          => $cart->min_quantity,
                    'is_digital_product'    => $cart->product->is_digital,
                    'variant'               => nullCheck($cart->variant),
                    'is_buy_now'            => (bool)$cart->is_buy_now,
                ];
            }
        }
        return $cart_list;
    }
}
