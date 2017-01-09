Contoh GraphQL pada PHP dengan MySQL dan PDO
======================================================================

## Apa itu GraphQL?

Singkatnya GraphQL adalah sebuah bahasa query (query language)
untuk API. GraphQL memberikan kemudahan pada client-side untuk mendapatkan data yang dibutuhkan.

Contoh, kamu menginginkan data detail produk dengan daftar review produk tersebut. Pada mekanisme REST biasa, kamu akan membutuhkan beberapa endpoint seperti:

* `api/products/{id}/detail`: untuk mengambil data detail produk
* `api/products/{id}/reviews`: untuk mengambil daftar review produk

Atau sebuah endpoint khusus untuk menangani kasus tersebut, semisal:

* `api/products/{id}/detail-with-reviews`

Masalah pada mekanisme REST tersebut adalah:

* Bagaimana jika kamu mengiginkan data produk dengan toko yang memiliki produk tsb?
* Bagaimana jika kamu menginginkan data produk lengkap dengan daftar image produk tersebut?
* Bagaimana jika kamu menginginkan data produk dengan review beserta user lengkap dengan nama dan emailnya?

Yang terjadi adalah kamu membutuhkan banyak endpoint untuk menangani kasus-kasus tersebut. Dan jangan lupa, pada setiap endpoint kamu harus melakukan validasi, filtrasi, resolving, dsb. Hal itu sangat merepotkan.

Dengan GraphQL kamu cukup membuat sebuah endpoint untuk menangani berbagai kemungkinan tersebut.

## GraphQL pada PHP

Pada repository ini, saya menggunakan library '[webonyx/graphql-php](https://github.com/webonyx/graphql-php)'. Untuk data resourcenya menggunakan database MySQL dan ekstensi PDO. Untuk datanya sendiri saya generate menggunakan library [faker](https://github.com/fzaninotto/Faker).

## Kebutuhan

* PHP >= v5.5
* MySQL >= v5 atau MariaDB >= v5
* Ekstensi pdo-mysql
* Composer (untuk install library graphql)

Sebetulnya saya kurang tau persis kebutuhannya seperti apa. Diatas itu nebak-nebak aja :p

Saya sendiri menggunakan software dengan spesifikasi sebagai berikut:

* PHP: v7.0.14
* Database: MariaDB v10.1.20
* OS: Ubuntu 14.04
* Server: PHP built-in server

Dicoba-coba aja. Untuk PHP sendiri versi 5.5 sepertinya bisa. Untuk server seharusnya apache, nginx, dsb juga bisa.

## Instalasi

#### Pengguna Windows dan XAMPP

* Clone atau download repository ini, taruh ke folder proyek kamu (biasanya di `C:/xampp/htdocs`). Note: taruhnya ke folder baru, bukan di `htdocs`nya.
* Buka xampp control. Start apache dan mysql-nya.
* Buka phpmyadmin, buat database baru dengan nama `contoh_graphql_php`.
* Import `db.sql` ke database tersebut.
* Buka cmd, masuk ke direktori proyeknya. Ketik `composer install`.

#### Pengguna PHP Built-in Server

* Clone atau download repository ini, taruh kemana aja bebas.
* Buat database baru dengan nama `contoh_graphql_php`.
* Import `db.sql` ke database tersebut.
* Buka cmd/terminal, masuk ke direktori tersebut. Ketik `composer install`.
* Masih di cmd/terminal dan direktori tersebut, jalankan server dengan perintah `php -S localhost:3000`.

#### Konfigurasi

Ada beberapa hal yang harus disesuaikan sebelum nyoba. Buka file `graphql.php`, lalu sesuaikan beberapa hal berikut:

* `$dbUsername`: isi dengan username database kamu.
* `$dbPassword`: isi dengan password database kamu.
* `BASE_URL`: nggak penting sih, tapi sesuaikan aja gpp. Misal pengguna XAMPP, ubah dengan `localhost/contoh-graphql`.

## Jalankan GraphQL

Disini karena saya menggunakan php built-in server, jadi endpoint graphql saya berada di url `http://localhost:3000/graphql.php`. Untuk kamu pengguna XAMPP silahkan akses `localhost/<foldernya>/graphql.php`.

Untuk mencoba graphql, silahkan buka browser, cobalah beberapa url berikut:

* `http://localhost:3000/graphql.php`: akan menampilkan "Hello World".
* `http://localhost:3000/graphql.php?query={user(id:5){id,name,email}}`: akan menampilkan JSON berisi 'id', 'name', dan 'email' user yang memiliki id 5.

Untuk mencoba query yang lebih rumit, umumnya developer akan menggunakan '[Graph_i_QL](https://github.com/graphql/graphiql)'. Tapi karena setupnya yang cukup memakan waktu, kamu dapat memakai ekstensi google chrome [GraphiQL Feen]((https://chrome.google.com/webstore/detail/graphiql-feen/mcbfdonlkfpbfdpimkjilhdneikhfklp)

Setelah menginstall, buka ekstensi tersebut, masuk ke tab server, masukkan server url menjadi `http://localhost:3000/graphql.php`. 

Kemudian silahkan coba query dibawah ini:

```graphql
{
  
  user(id: 5) {
    id
    name
    email
  }
  product(id: 10) {
    id
    name
    url_thumbnail
    images {
      id
      url
    }
    reviews {
      message
      star
      user {
        name
        id
      }
    }
  }
  products(limit:5) {
    id
    slug
    name
    weight
    price
  }
  productCategories {
    id
    slug
    name
    products(limit:3) {
      id
      name
      slug
    }
  }
}
```


Hasilnya akan seperti ini:

![Contoh hasil](https://github.com/emsifa/contoh-graphql-php-mysql/raw/master/img/ss.png)

## Penutup

GraphQL adalah [masa depan](https://dev-blog.apollodata.com/why-graphql-is-the-future-3bec28193807#.s66nhjrbh) pengembangan website. Untuk kamu yang tertarik dengan graphql, saya menaruh penjelasan pada scriptnya. Pada `graphql.php` juga saya menyertakan beberapa bahan latihan yang ditandai dengan `@Latihan`. Silahkan coba jika kamu merasa tertantang.

Apa yang saya coba disini adalah dasarnya, ada beberapa _bad practice_ yang seharusnya diperbaiki.

Untuk langkah selanjutnya, silahkan pelajari [DataLoader](https://github.com/facebook/dataloader) untuk optimasi pada sisi server (versi PHP-nya [disini](https://github.com/overblog/dataloader-php)). Untuk kalian yang suka menggunakan React.js, silahkan pelajari [relay](https://facebook.github.io/relay/) (library tambahan server-side PHP-nya [disini](https://github.com/ivome/graphql-relay-php)).
