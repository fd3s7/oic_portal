<?php

namespace App\Service;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Goutte\Client;


class CrawlService
{
    /**
     * クライアントを作成
     * @return Client
     */
    public function makeCrient()
    {
        $client = new Client();

        return $client;
    }

    /**
     * 初期設定
     */
    public function setDefault($site)
    {
        // TARGET PATH
        $site_id = $site->id;
        $url = $site->news_site_url;    // サイト自体のURL
        $tag_for_url = $site->news_site_tag_url;    // 記事URL
        $tag_for_title = $site->news_site_tag_title;
        $tag_for_image = $site->news_site_tag_image;
        $tag_for_text = $site->news_site_tag_text;

        return compact('site_id','url','tag_for_url','tag_for_title','tag_for_image','tag_for_text');

    }

    /**
     * 記事一覧を取得
     * @param $client
     * @param $url
     * @param $tag_for_url
     * @return mixed
     */
    public function getLists($client,$url,$tag_for_url)
    {
        $crawler = $client->request('GET', $url);

        // 記事URL取得
        $urls = $crawler->filter($tag_for_url)->each(function ($node) {
            $results = $node->text();
            return $results;
        });

        return $urls;
    }

    /**
     * 記事を取得
     * @param $client
     * @param $url
     * @return mixed
     */
    public function getContents($client,$url)
    {
        $crawler = $client->request('GET', $url);

        return $crawler;
    }

    /**
     * 記事タイトルを取得（一覧ページから取得）
     * @param $contents
     * @param $tag_for_title
     * @return mixed
     */
    public function getTitle($contents,$tag_for_title)
    {
        $title = $contents->filter($tag_for_title)->each(function ($node) {
            $results = $node->text();
            return $results;
        });

        return $title;
    }

    /**
     * 記事画像を取得
     * @param $contents
     * @param $tag_for_image
     * @return mixed
     */
    public function getImages($contents,$tag_for_image)
    {
        $image = $contents->filter($tag_for_image)->each(function ($node) {
            $results = $node->attr('src');
            return $results;
        });

        if(!is_array($image)){
            return "error";
        }

        if(count($image) > 0){
            return $image[0];
        }

        return "error";
    }

    /**
     * 記事本文を取得
     * @param $contents
     * @param $tag_for_text
     * @return mixed
     */
    public function getText($contents,$tag_for_text)
    {
        $text = $contents->filter($tag_for_text)->each(function ($node) {
            $results = $node->text();
            return $results;
        });

        return $text;
    }

    /**
     * 不要文字列除去
     * @param $client
     * @param $url
     * @return mixed
     */
    public function replaceWord($word)
    {
        $blackwords = DB::table('articles_exclusion')->get();
        foreach($blackwords as $blackword){
            $word = str_replace($blackword->exclusion_string, '',$word);
        }
        return $word;
    }

    /**
     * サイトでDBに格納されている最新記事のURLを返却
     * @param $site_id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getLatest2ArticleUrlByDB($site_id)
    {
        $results = DB::table('articles_table')->where('news_site_id','=',$site_id)->orderBy('id','desc')->select('articles_table.article_url')->limit(2)->get();
        return $results;
    }

    public function checkNewArticle($latest_article_url,$urls)
    {
        // 記事がなければ
        if(array_search($latest_article_url[0]->article_url,$urls) === false){
            // 記事がなかった時の処理
        }

        $id = DB::table('articles_table')->where('article_url')->select('id')->first();
        dd($id);

        if(array_search($latest_article_url[1]->article_url,$urls) === false){
            // 記事２がなかった場合の処理

        }

    }

    /**
     * @param $urls : サイトから取得した新着記事集
     * @param $site_id : ウェブサイトID (指定することでSQL速度向上
     * @return array : 配列で返却
     */
    public function checkUrlInDB($urls,$site_id)
    {
        $new_urls = [];
        $debug = [];
        foreach($urls as $url) {
            $isExsists = DB::table('articles_table')
                    ->where('news_site_id',$site_id)
                    ->where('article_url',$url)
                ->first();

            if($isExsists){
                $debug[] = $url;
            } else {
                $new_urls[] = $url;
            }
        }

//        $debug_sql = DB::table('articles_table')->where('news_site_id',2)->select('article_url')->get();
        //return compact('new_urls');
        return $new_urls;
       // dd($debug,$new_urls,$debug_sql);
   //     return compact('new_urls','debug');

    }




    /*
     * 以下、動作テスト用
     */

    public function get_contents($client,$url)
    {
        $crawler = $client->request('GET', $url);

        return $crawler;
    }

    public function get_article_images($client,$url, $image_custom_tag)
    {
        $crawler = $this->get_contents($client,$url);
        dd($crawler);

        $crawler = $client->request('GET', $url);
        dd($crawler);

        $article_images = $crawler->filter($image_custom_tag)->each(function ($node) {
            $results = $node->attr('src');
            return $results;
        });

        return $article_images;
    }

    public function get_article_texts()
    {

    }


}