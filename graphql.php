<?php

require_once 'vendor/autoload.php';

use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Error\FormattedError;
use GraphQL\Type\Definition\ObjectType;

// #1 PERSIAPAN
// ===============================================================================================

// ## 1.0 Definisikan Konstanta yg dibutuhkan
// -----------------------------------------------------------------------------------------------
// ini dipakai di ProductImageType dan ProductType untuk ambil url image dan thumbnail
define('BASE_URL', 'http://localhost:3000');

// ## 1.1 Non-aktifkan error reporting
// -----------------------------------------------------------------------------------------------
// karena library ini memiliki debugger khusus
ini_set('display_errors', 0);

// ## 1.2 Aktifkan mode debugging jika terdapat query debug (e.g: url.php?debug)
// -----------------------------------------------------------------------------------------------
$debug = !empty($_GET['debug']);
if ($debug) {
    $phpErrors = [];
    // simpan error kedalam $phpErrors untuk nantinya dihandle pada bagian bawah
    set_error_handler(function($severity, $message, $file, $line) use (&$phpErrors) {
        $phpErrors[] = new ErrorException($message, 0, $severity, $file, $line);
    });
}

try {
    // ## 1.3 Siapkan koneksi PDO 
    // -----------------------------------------------------------------------------------------------
    $dbHost     = "localhost";
    $dbUsername = "emsifa";
    $dbPassword = "emsifa";
    $dbName     = "contoh_graphql_php";
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ## 1.4 Menyiapkan app context
    // -----------------------------------------------------------------------------------------------
    // app context disini dapat berupa apapun
    // entah itu objek, array, string, dsb
    // app context disini akan dikirimkan oleh library graphql 
    // untuk dapat kita manfaatkan dalam mengolah/mengambil data
    $appContext = [
        'user_id' => null, // ceritanya ID user yg login, NULL = user belum login
        'pdo' => $pdo // untuk nantinya dipakai dalam melakukan query select
    ];

    // ## 1.5 Mengambil data yang dikirimkan client
    // -----------------------------------------------------------------------------------------------
    // data disini dapat berisi query dan variables
    // query = graphql query
    // variables = variable tambahan

    // jika request header content_type adalah 'application/json' ...
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        // ... ambil data berdasarkan php://input (request body)
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
    } else {
        // kalau bukan, ambil data berdasarkan $_REQUEST ($_POST dan $_GET)
        $data = $_REQUEST;
    }
    // merge data (memastikan supaya tidak terjadi error undefined index array)
    $data += ['query' => null, 'variables' => null];
    // jika tidak terdapat query pada data ...
    if (null === $data['query']) {
        // ... set query menjadi '{hello}' (default query)
        $data['query'] = '{hello}';
    }

    // #2 MEMPERSIAPKAN TYPE & SKEMA
    // ===============================================================================================
    
    // ## 2.1 Load Types
    // -----------------------------------------------------------------------------------------------
    // Load beberapa type pada file lain
    // supaya tidak membingungkan pemula
    // disini kita tidak menggunakan autoloader (bahkan namespace), 
    // jadi require manual 1/1 aja :D
    require __DIR__ . '/types/UserType.php';
    require __DIR__ . '/types/ProductType.php';
    require __DIR__ . '/types/ProductCategoryType.php';
    require __DIR__ . '/types/ProductReviewType.php';
    require __DIR__ . '/types/ProductImageType.php';
    require __DIR__ . '/Types.php';

    // ## 2.2 Membuat Query Type
    // -----------------------------------------------------------------------------------------------
    // query type adalah type yg digunakan untuk root node
    // saya definisikan disini (bukan pada class & file lain seperti type-type di atas)
    // supaya kalian dapat lebih mudah membedakan scope penggunaannya
    $queryType = new ObjectType([
        'name' => 'Query',
        'fields' => [
            'hello' => [
                'description' => 'Contoh hello world',
                'type' => Types::string(),
                'resolve' => function() {
                    return 'Hello World';
                }
            ],
            'user' => [
                'description' => 'Data user berdasarkan ID',
                'type' => Types::user(),
                'args' => [
                    'id' => Types::nonNull(Types::int())
                ],
                'resolve' => function($rootValue, $args, $context) {
                    $pdo = $context['pdo'];
                    // disini $args['id'] sudah pasti integer, 
                    $id = $args['id'];
                    // jadi tidak menggunakan prepared statement juga tidak apa
                    $result = $pdo->query("select * from users where id = {$id}");
                    return $result->fetchObject() ?: null;
                }
            ],
            'product' => [
                'description' => 'Data produk berdasarkan ID',
                'type' => Types::product(),
                'args' => [
                    'id' => Types::nonNull(Types::int())
                ],
                'resolve' => function($rootValue, $args, $context) {
                    $pdo = $context['pdo'];
                    $id = $args['id'];
                    $result = $pdo->query("select * from products where id = {$id}");
                    return $result->fetchObject() ?: null;
                }
            ],
            'products' => [
                'description' => 'Data list produk',
                'type' => Types::listOf(Types::product()),
                'args' => [
                    // argumen untuk keperluan paging
                    'offset' => Types::int(),
                    'limit' => Types::int()
                ],
                'resolve' => function($rootValue, $args, $context) {
                    $pdo = $context['pdo'];
                    // limit dan offset disini antara int atau null
                    // jadi kalau null, set ke angka defaultnya untuk memastikan
                    // nilainya adalah int
                    $limit = $args['limit'] ?: 10;
                    $offset = $args['offset'] ?: 0;

                    // memastikan limit tidak lebih dari 50
                    // untuk mencegah user memasukkan limit berlebihan
                    // yg dapat membuat server kewalahan
                    if ($limit > 50) $limit = 50;

                    // @Latihan: coba buat 'order by'-nya dinamis
                    $result = $pdo->query("select * from products order by id desc limit {$limit} offset {$offset}");
                    return $result->fetchAll(PDO::FETCH_OBJ);
                }
            ],
            'productCategories' => [
                'description' => 'Data list kategori produk',
                'type' => Types::listOf(Types::productCategory()),
                'resolve' => function($rootValue, $args, $context) {
                    $pdo = $context['pdo'];
                    $result = $pdo->query("select * from product_categories order by name asc");
                    return $result->fetchAll(PDO::FETCH_OBJ);
                }
            ],
            // @Latihan: coba daftarkan node 'productReviews' yg berisi list review produk berdasarkan id produk
        ]
    ]);


    // # 2.3 Membuat Skema
    // -----------------------------------------------------------------------------------------------
    // Ada beberapa key yang dapat digunakan pada parameter pertama Schema, 
    // diantaranya adalah 'query' dan 'mutation'
    // key 'query' digunakan untuk melakukan operasi read (R dari CRUD),
    // sedangkan key 'mutation' digunakan untuk operasi write (CUD dari CRUD)
    // disini kita hanya menggunakan 'query' atau select data saja
    $schema = new Schema([
        'query' => $queryType
    ]);

    // #3 Eksekusi GraphQL
    // ===============================================================================================
    $result = GraphQL::execute(
        $schema,
        $data['query'],
        null,
        $appContext,
        (array) $data['variables']
    );

    // #4 Memasukkan Error kedalam $result (kalo ada)
    // ===============================================================================================
    if ($debug && !empty($phpErrors)) {
        $result['extensions']['phpErrors'] = array_map(
            ['GraphQL\Error\FormattedError', 'createFromPHPError'],
            $phpErrors
        );
    }
    $httpStatus = 200;
} catch (\Exception $error) {
    // #5 Handling Exception
    // ===============================================================================================
    $httpStatus = 500;
    if (!empty($_GET['debug'])) {
        $result['extensions']['exception'] = FormattedError::createFromException($error);
    } else {
        $result['errors'] = [FormattedError::create('Unexpected Error')];
    }
}

// #6 Tampilkan Hasilnya (Berupa JSON)
// ===============================================================================================
header('Content-Type: application/json', true, $httpStatus);
echo json_encode($result);
