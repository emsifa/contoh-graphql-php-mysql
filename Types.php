<?php


use GraphQL\Type\Definition\Type;

/**
 * Class Types
 * 
 * Class ini digunakan untuk mempermudah mengambil instance
 * dari type yang tersedia pada graphql yang kita buat
 * 
 * Instance yang diambil disini dipakai untuk 
 * mendefinisikan type ke setiap node pada graphql
 *
 * Karena pada kasus ini semua type yang dibuat bersifat sama
 * contoh: User pada semua node memiliki fields yang sama
 * jadi instance yang dibuat disini  bersifat singleton
 * untuk lebih menghemat penggunaan memory
 */
class Types extends Type
{

    protected static $typeInstances = [];

    public static function user()
    {
        return static::getInstance(UserType::class);
    }

    public static function product()
    {
        return static::getInstance(ProductType::class);
    }

    public static function productImage()
    {
        return static::getInstance(ProductImageType::class);
    }

    public static function productReview()
    {
        return static::getInstance(ProductReviewType::class);
    }

    public static function productCategory()
    {
        return static::getInstance(ProductCategoryType::class);
    }

    protected static function getInstance($class, $arg = null)
    {
        if (!isset(static::$typeInstances[$class])) {
            $type = new $class($arg);
            static::$typeInstances[$class] = $type;
        }

        return static::$typeInstances[$class];
    }
}
