<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('/','/home/Index/index');

// Route::get('hello/:name', 'index/hello');
// 显示后台页面
Route::get('admin_index','/admin/Index/index');
// 我的桌面
Route::get('admin_welcome',"/admin/Index/welcome");
/**
 * 商品分类
 */
// 显示后台分类
Route::get('admin_product_category','/admin/goods/product_category');
// 商品分类添加表
Route::get('admin_productCatAdd','/admin/goods/product_category_add');
// 分类 ajax 
Route::get('admin_product_category_ajax','/admin/goods/product_category_ajax');
// 添加分类信息到数据库
Route::get('admin_goods_type_add','/admin/goods/goods_type_add');

/**
 * 商品
 */
// 商品列表
Route::get('admin_product_list','/admin/goods/product_list');
// 增加商品
Route::get('admin_produce_add','/admin/goods/product_add');
