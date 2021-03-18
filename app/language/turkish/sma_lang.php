<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Module: General Language File for common lang keys
 * Language: English
 *
 * Last edited:
 * 30th April 2015
 *
 * Package:
 * Stock Manage Advance v3.0
 *
 * You can translate this file to your language.
 * For instruction on new language setup, please visit the documentations.
 * You also can share your language files by emailing to saleem@tecdiary.com
 * Thank you
 */

/* --------------------- CUSTOM FIELDS ------------------------ */
/*
* Below are custome field labels
* Please only change the part after = and make sure you change the the words in between "";
* $lang['bcf1']                         = "Biller Custom Field 1";
* Don't change this                     = "You can change this part";
* For support email contact@tecdiary.com Thank you!
*/

$lang['bcf1']                                         = "Fatura Özel Alan 1";
$lang['bcf2']                                         = "Fatura Özel Alan 2";
$lang['bcf3']                                         = "Fatura Özel Alan 3";
$lang['bcf4']                                         = "Fatura Özel Alan 4";
$lang['bcf5']                                         = "Fatura Özel Alan 5";
$lang['bcf6']                                         = "Fatura Özel Alan 6";
$lang['pcf1']                                         = "Ürün Özel Alan 1";
$lang['pcf2']                                         = "Ürün Özel Alan 2";
$lang['pcf3']                                         = "Ürün Özel Alan 3";
$lang['pcf4']                                         = "Ürün Özel Alan 4";
$lang['pcf5']                                         = "Ürün Özel Alan 5";
$lang['pcf6']                                         = "Ürün Özel Alan 6";
$lang['ccf1']                                         = "Müşteri Özel Alan 1";
$lang['ccf2']                                         = "Müşteri Özel Alan 2";
$lang['ccf3']                                         = "Müşteri Özel Alan 3";
$lang['ccf4']                                         = "Müşteri Özel Alan 4";
$lang['ccf5']                                         = "Müşteri Özel Alan 5";
$lang['ccf6']                                         = "Müşteri Özel Alan 6";
$lang['scf1']                                         = "Tedarikçi Özel Alan 1";
$lang['scf2']                                         = "Tedarikçi Özel Alan 2";
$lang['scf3']                                         = "Tedarikçi Özel Alan 3";
$lang['scf4']                                         = "Tedarikçi Özel Alan 4";
$lang['scf5']                                         = "Tedarikçi Özel Alan 5";
$lang['scf6']                                         = "Tedarikçi Özel Alan 6";

/* ----------------- DATATABLES LANGUAGE ---------------------- */
/*
* Below are datatables language entries
* Please only change the part after = and make sure you change the the words in between "";
* 'sEmptyTable'                     => "No data available in table",
* Don't change this                 => "You can change this part but not the word between and ending with _ like _START_;
* For support email support@tecdiary.com Thank you!
*/

$lang['datatables_lang']                              = array(
  'sEmptyTable'                   => "Tabloda veri yok",
  'sInfo'                         => "Gösterilen _START_ ile _END_ arası, toplam _TOTAL_ giriş",
  'sInfoEmpty'                    => "Gösterilen 0 ile 0 arası, toplam 0 giriş",
  'sInfoFiltered'                 => "(filtrelenen toplam _MAX_ giriş)",
  'sInfoPostFix'                  => "",
  'sInfoThousands'                => ",",
  'sLengthMenu'                   => "Göster _MENU_ ",
  'sLoadingRecords'               => "Yükleniyor...",
  'sProcessing'                   => "İşleniyor...",
  'sSearch'                       => "Arama",
  'sZeroRecords'                  => "Kayıt bulunamadı",
  'oAria'                                     => array(
    'sSortAscending'                => ": sütunlarda artan sıralama etkin",
    'sSortDescending'               => ": sütunlarda azalan sıralama etkin"
  ),
  'oPaginate'                                 => array(
    'sFirst'                        => "<< İlk",
    'sLast'                         => "Son >>",
    'sNext'                         => "Sonraki >",
    'sPrevious'                     => "< Önceki",
  )
);


/* ----------------- Select2 LANGUAGE ---------------------- */
/*
* Below are select2 lib language entries
* Please only change the part after = and make sure you change the the words in between "";
* 's2_errorLoading'                 => "The results could not be loaded",
* Don't change this                 => "You can change this part but not the word between {} like {t};
* For support email support@tecdiary.com Thank you!
*/

$lang['select2_lang']                                 = array(
  'formatMatches_s'               => "Bir sonuç bulundu seçmek için enter'a basınız.",
  'formatMatches_p'               => "Kullanınabilir sonuçlarda gezmek için yukarı/aşağı ok/yön tuşlarını kullanabilirsiniz.",
  'formatNoMatches'               => "Sonuç bulunamadı",
  'formatInputTooShort'           => "Lütfen {n} veya daha fazla karakter girin",
  'formatInputTooLong_s'          => "Lütfen {n} daha fazla karakter silin",
  'formatInputTooLong_p'          => "Lütfen {n} karakterleri silin",
  'formatSelectionTooBig_s'       => "Sadece {n} öğe seçebilirisiniz",
  'formatSelectionTooBig_p'       => "Sadece {n} öğeleri seçebilirisiniz",
  'formatLoadMore'                => "Diğer sonuçlar yükleniyor...",
  'formatAjaxError'               => "Ajax isteği başarısız",
  'formatSearching'               => "Aranıyor..."
);

/* ----------------- SMA GENERAL LANGUAGE KEYS -------------------- */

$lang['home']                                         = "Ana Sayfa";
$lang['dashboard']                                    = "Pano";
$lang['username']                                     = "Kullanıcı Adı";
$lang['password']                                     = "Şifre";
$lang['first_name']                                   = "Adı";
$lang['last_name']                                    = "Soyadı";
$lang['confirm_password']                             = "Şifre Onayla";
$lang['email']                                        = "E-Posta";
$lang['phone']                                        = "Telefon";
$lang['company']                                      = "Firma";
$lang['product_code']                                 = "Ürün Kodu";
$lang['product_name']                                 = "Ürün Adı";
$lang['cname']                                        = "Müşteri Adı";
$lang['barcode_symbology']                            = "Barkod Simge";
$lang['product_unit']                                 = "Ürün Birim";
$lang['product_price']                                = "Ürün Fiyat";
$lang['contact_person']                               = "İlgili kişi";
$lang['email_address']                                = "E-posta Adresi";
$lang['address']                                      = "Adres";
$lang['city']                                         = "Şehir";
$lang['today']                                        = "Bugün";
$lang['welcome']                                      = "Hoşgeldiniz";
$lang['profile']                                      = "Profil";
$lang['change_password']                              = "Şifre Değiştir";
$lang['logout']                                       = "Çıkış";
$lang['notifications']                                = "Duyurular";
$lang['calendar']                                     = "Takvim";
$lang['messages']                                     = "Mesajlar";
$lang['styles']                                       = "Stiller";
$lang['language']                                     = "Dil";
$lang['alerts']                                       = "Uyarılar";
$lang['list_products']                                = "Ürünleri Listele";
$lang['add_product']                                  = "Ürün Ekle";
$lang['print_barcodes']                               = "Barkodları Bastır";
$lang['print_labels']                                 = "Baskı Etiketleri";
$lang['import_products']                              = "Ürünleri İçe Aktar";
$lang['update_price']                                 = "Fiyat Güncelleme";
$lang['damage_products']                              = "Hasarlı Ürün";
$lang['sales']                                        = "Satışlar";
$lang['list_sales']                                   = "Satışları Listele";
$lang['add_sale']                                     = "Satış Ekle";
$lang['deliveries']                                   = "Teslimatlar";
$lang['gift_cards']                                   = "Hediye Kartlar";
$lang['quotes']                                       = "Teklif/Aktarımlar";
$lang['list_quotes']                                  = "Teklif/Aktarımları Listele";
$lang['add_quote']                                    = "Teklif/Aktarım Ekle";
$lang['purchases']                                    = "Alımlar";
$lang['list_purchases']                               = "Alımları Listele";
$lang['add_purchase']                                 = "Alım Ekle";
$lang['add_purchase_by_csv']                          = "CSV ile Alım EKle";
$lang['transfers']                                    = "Transferler";
$lang['list_transfers']                               = "Transflerleri Listele";
$lang['add_transfer']                                 = "Transfer Ekle";
$lang['add_transfer_by_csv']                          = "CSV ile Transfer Ekle";
$lang['people']                                       = "İnsanlar";
$lang['list_users']                                   = "Kullanıcı Listesi";
$lang['new_user']                                     = "Kullanıcı Ekle";
$lang['list_billers']                                 = "Faturaları Listele";
$lang['add_biller']                                   = "Fatura Ekle";
$lang['list_customers']                               = "Müşterileri Listele";
$lang['add_customer']                                 = "Müşteri Ekle";
$lang['list_suppliers']                               = "Tedarikçileri Listele";
$lang['add_supplier']                                 = "Tedarikçi Ekle";
$lang['settings']                                     = "Ayarlar";
$lang['system_settings']                              = "Sistem Ayarları";
$lang['change_logo']                                  = "Logo Değiştir";
$lang['currencies']                                   = "Kurlar";
$lang['attributes']                                   = "Ürün Türevleri";
$lang['customer_groups']                              = "Müşteri Grupları";
$lang['categories']                                   = "Kategoriler";
$lang['subcategories']                                = "Alt Kategoriler";
$lang['tax_rates']                                    = "Vergi oranları";
$lang['warehouses']                                   = "Depo/Şubeler";
$lang['email_templates']                              = "E-Posta Şablonları";
$lang['group_permissions']                            = "Grup İzinleri";
$lang['backup_database']                              = "Veritabanı Yedekle";
$lang['reports']                                      = "Raporlar";
$lang['overview_chart']                               = "Tablo Önzileme";
$lang['warehouse_stock']                              = "Depo/Şube Stok Tablosu";
$lang['product_quantity_alerts']                      = "Ürün Miktarı Uyarıları";
$lang['product_expiry_alerts']                        = "Ürün Vade Uyarıları";
$lang['products_report']                              = "Ürün Raporları";
$lang['daily_sales']                                  = "Günlük Satışlar";
$lang['monthly_sales']                                = "Aylık Satışlar";
$lang['sales_report']                                 = "Satış Raporları";
$lang['payments_report']                              = "Ödeme Raporları";
$lang['profit_and_loss']                              = "Kar ve/veya Zararlar";
$lang['purchases_report']                             = "Alım Raporları";
$lang['customers_report']                             = "Müşteri Raporları";
$lang['suppliers_report']                             = "Tedarikçi Raporları";
$lang['staff_report']                                 = "Personel Raporu";
$lang['your_ip']                                      = "IP Adresiniz";
$lang['last_login_at']                                = "Son giriş zamanı";
$lang['notification_post_at']                         = "Bildirim yayınlayan";
$lang['quick_links']                                  = "Hızlı Linkler";
$lang['date']                                         = "Tarih";
$lang['reference_no']                                 = "Referans No";
$lang['products']                                     = "Ürünler";
$lang['customers']                                    = "Müşteriler";
$lang['suppliers']                                    = "Tedarikçiler";
$lang['users']                                        = "Kullanıcılar";
$lang['latest_five']                                  = "Son Beş";
$lang['total']                                        = "Toplam";
$lang['payment_status']                               = "Ödeme durumu";
$lang['paid']                                         = "Ücretli";
$lang['customer']                                     = "Müşteri";
$lang['status']                                       = "Durum";
$lang['amount']                                       = "Miktar";
$lang['supplier']                                     = "Teradikçi";
$lang['from']                                         = "Kimden";
$lang['to']                                           = "Kime";
$lang['name']                                         = "Adı";
$lang['create_user']                                  = "Kullanıcı Adı";
$lang['gender']                                       = "Cinsiyet";
$lang['biller']                                       = "Fatura";
$lang['select']                                       = "Seç";
$lang['warehouse']                                    = "Depo/Şube";
$lang['active']                                       = "Aktif";
$lang['inactive']                                     = "Pasif";
$lang['all']                                          = "Hepsi";
$lang['list_results']                                 = "Sonuçları gezinmek veya süzmek için aşağıdaki tabloyu kullanın. Tabloyu excel veya pdf olarak indirebilirisiniz.";
$lang['actions']                                      = "Eylemler";
$lang['pos']                                          = "POS";
$lang['access_denied']                                = "Erişim Engellendi! İstenen sayfaya erişim hakkına sahip değilsiniz. Eğer bunun yanlışlıkla olduğunu düşünüyorsanız,lütfen sistem yöneticisi ile iletişime geçiniz.";
$lang['add']                                          = "Ekle";
$lang['edit']                                         = "Düzenle";
$lang['delete']                                       = "Sil";
$lang['view']                                         = "Görüntüle";
$lang['update']                                       = "Güncelle";
$lang['save']                                         = "Kaydet";
$lang['login']                                        = "Giriş";
$lang['submit']                                       = "Gönder";
$lang['no']                                           = "Hayır";
$lang['yes']                                          = "Evet";
$lang['disable']                                      = "Pasif";
$lang['enable']                                       = "Aktif";
$lang['enter_info']                                   = "Aşağıdaki bilgileri doldurunuz. * ile işaretlenmiş alanların doldurulması zorunludur.";
$lang['update_info']                                  = "Aşağıdaki bilgileri doldurunuz.  * ile işaretlenmiş alanların doldurulması zorunludur.";
$lang['no_suggestions']                               = "Öneriler için veri alınamıyor, Lütfen girişi kontrol ediniz";
$lang['i_m_sure']                                     = 'Evet eminim';
$lang['r_u_sure']                                     = 'Eminmisiniz?';
$lang['export_to_excel']                              = "Excel dosyasına aktar";
$lang['export_to_pdf']                                = "PDF dosyasına aktar";
$lang['image']                                        = "Resim";
$lang['sale']                                         = "Satış";
$lang['quote']                                        = "Teklif/Aktarım";
$lang['purchase']                                     = "Satınalma";
$lang['transfer']                                     = "Transfer";
$lang['payment']                                      = "Ücret";
$lang['payments']                                     = "Ücretler";
$lang['orders']                                       = "Siparişler";
$lang['pdf']                                          = "PDF";
$lang['vat_no']                                       = "KDV Numarası";
$lang['country']                                      = "Ülke";
$lang['add_user']                                     = "Kullanıcı Ekle";
$lang['type']                                         = "Tip";
$lang['person']                                       = "Kişi";
$lang['state']                                        = "Semt";
$lang['postal_code']                                  = "Posta Kodu";
$lang['id']                                           = "ID";
$lang['close']                                        = "Kapat";
$lang['male']                                         = "Erkek";
$lang['female']                                       = "Kadın";
$lang['notify_user']                                  = "Kullanıcı bildir";
$lang['notify_user_by_email']                         = "Kullanıcıyı E-posta ile haberdar et";
$lang['billers']                                      = "Fatura Adları";
$lang['all_warehouses']                               = "Tüm Depo/Şubeler";
$lang['category']                                     = "Kategori";
$lang['product_cost']                                 = "Ürün Maliyeti";
$lang['quantity']                                     = "Miktar";
$lang['loading_data_from_server']                     = "Veriler sunucudan yükleniyor";
$lang['excel']                                        = "Excel";
$lang['print']                                        = "Yazdır";
$lang['ajax_error']                                   = "Ajax hatası oluştu, Lütfen tekrar deneyin.";
$lang['product_tax']                                  = "Ürün Vergisi";
$lang['order_tax']                                    = "Sipariş Vergisi";
$lang['upload_file']                                  = "Dosya yükle";
$lang['download_sample_file']                         = "Örnek Dosya İndir";
$lang['csv1']                                         = "İndirilen csv dosyasına ilk satırı olduğu gibi kalmalıdır. Sütunların sırasını lütfen değiştirmeyin.";
$lang['csv2']                                         = "Doğru sütun sırası";
$lang['csv3']                                         = "&amp; Bunu takip edin. Eğer İngilizce başka bir dil kullanıyorsanız, csv dosyası UTF-8 kodlu ve bayt sırası işareti ile kaydedilir ve (BOM)suz olmalıdır.";
$lang['import']                                       = "İçe Aktar";
$lang['note']                                         = "Not";
$lang['grand_total']                                  = "Genel Toplam";
$lang['download_pdf']                                 = "PDF olarak Kaydet";
$lang['no_zero_required']                             = "%s alanı gereklidir";
$lang['no_product_found']                             = "Ürün bulunamadı";
$lang['pending']                                      = "Bekliyor";
$lang['sent']                                         = "Gönder";
$lang['completed']                                    = "Tamamlandı";
$lang['canceled']                                     = "Iptal edildi";
$lang['shipping']                                     = "Kargo";
$lang['add_product_to_order']                         = "Ürünleri sipariş listesine ekleyiniz";
$lang['order_items']                                  = "Sipariş Öğeleri";
$lang['net_unit_cost']                                = "Net Birim Maliyeti";
$lang['net_unit_price']                               = "Net Birim Fiyatı";
$lang['expiry_date']                                  = "Son kullanma tarihi";
$lang['subtotal']                                     = "Ara toplam";
$lang['reset']                                        = "Sıfırla";
$lang['items']                                        = "Öğeler";
$lang['au_pr_name_tip']                               = "Lütfen yazmaya /kod/ürünadı ile başlayın veya bakodu taratınız.";
$lang['no_match_found']                               = "Eşleşen sonuç bulunamadı! Seçilen ürün Depo/şubede yok yada bitmiş olabilir.";
$lang['csv_file']                                     = "CSV Dosyası";
$lang['document']                                     = "Belge Ekle";
$lang['product']                                      = "Ürün";
$lang['user']                                         = "Kullanıcı";
$lang['created_by']                                   = "Oluşturan";
$lang['loading_data']                                 = "Tablolar sunucudan yükleniyor";
$lang['tel']                                          = "Tel";
$lang['ref']                                          = "Refererans";
$lang['description']                                  = "Açıklama";
$lang['code']                                         = "Kod";
$lang['tax']                                          = "Vergi";
$lang['unit_price']                                   = "Birim fiyat";
$lang['discount']                                     = "İndirim";
$lang['order_discount']                               = "Sipariş İnidirimi";
$lang['total_amount']                                 = "Toplam Tutarı";
$lang['download_excel']                               = "Excel olarak indir";
$lang['subject']                                      = "Konu";
$lang['cc']                                           = "CC";
$lang['bcc']                                          = "BCC";
$lang['message']                                      = "Mesaj";
$lang['show_bcc']                                     = "Göster/Gizle BCC";
$lang['price']                                        = "Fiyat";
$lang['add_product_manually']                         = "Ürün manüel ekle";
$lang['currency']                                     = "Para/Döviz";
$lang['product_discount']                             = "Ürün İndirimi";
$lang['email_sent']                                   = "E-posta başarıyla gönderildi";
$lang['add_event']                                    = "Etkinlik Ekle";
$lang['add_modify_event']                             = "Etkinlik Ekle / Düzenle";
$lang['adding']                                       = "Ekleniyor...";
$lang['delete']                                       = "Sil";
$lang['deleting']                                     = "Siliniyor...";
$lang['calendar_line']                                = "Düzenlemek istediğiniz etkiniğin tarihine tıklayın.";
$lang['discount_label']                               = "İnidirim (5/5%)";
$lang['product_expiry']                               = "Ürün Son Kullanma";
$lang['unit']                                         = "Birim";
$lang['cost']                                         = "Maliyet";
$lang['tax_method']                                   = "Vergi Yöntemi";
$lang['inclusive']                                    = "Dahil";
$lang['exclusive']                                    = "Özel";
$lang['expiry']                                       = "Geçerlilik";
$lang['customer_group']                               = "Müşteri Grubu";
$lang['is_required']                                  = "gerekli";
$lang['form_action']                                  = "Form İşlem";
$lang['return_sales']                                 = "İade Satışlar";
$lang['list_return_sales']                            = "İade Satışlar Listesi";
$lang['no_data_available']                            = "Veri yok";
$lang['disabled_in_demo']                             = "Özür dileriz, bu özellik demo olduğu için kullanılamıyor.";
$lang['payment_reference_no']                         = "Ödeme Referans No";
$lang['gift_card_no']                                 = "Hediye Kart No";
$lang['paying_by']                                    = "Ödeme Yapan";
$lang['cash']                                         = "Nakit";
$lang['gift_card']                                    = "Hediye Kartı";
$lang['CC']                                           = "Kredi Kartı";
$lang['cheque']                                       = "Çek";
$lang['cc_no']                                        = "Kredi Kart No";
$lang['cc_holder']                                    = "Sahibinin Adı";
$lang['card_type']                                    = "Kart Türü";
$lang['Visa']                                         = "Visa";
$lang['MasterCard']                                   = "MasterCard";
$lang['Amex']                                         = "Amex";
$lang['Discover']                                     = "Discover";
$lang['month']                                        = "Ay";
$lang['year']                                         = "Yıl";
$lang['cvv2']                                         = "CVV2";
$lang['cheque_no']                                    = "Çek No";
$lang['Visa']                                         = "Visa";
$lang['MasterCard']                                   = "MasterCard";
$lang['Amex']                                         = "Amex";
$lang['Discover']                                     = "Discover";
$lang['send_email']                                   = "E-Posta Gönder";
$lang['order_by']                                     = "Sipariş Edilen";
$lang['updated_by']                                   = "Güncelleyen";
$lang['update_at']                                    = "Güncelleme";
$lang['error_404']                                    = "404 Sayfa Bulunamadı ";
$lang['default_customer_group']                       = "Standart Müşteri Grubu";
$lang['pos_settings']                                 = "POS Ayarlar";
$lang['pos_sales']                                    = "POS Satışları";
$lang['seller']                                       = "Satıcı";
$lang['ip                                             : ']                                = "IP:";
$lang['sp_tax']                                       = "Satılan Ürün Vergisi";
$lang['pp_tax']                                       = "Satın alınan ürün Vergis";
$lang['overview_chart_heading']                       = "Maliyet ve fiyat (pasta) ile ürün vergiler ve sipariş vergisi (sütunlar), alımları (çizgi) ve mevcut stok değeri ile aylık satış da dahil olmak üzere tablo ödemelerini  jpg, png ve pdf olarak kayıt edebilirsiniz.";
$lang['stock_value']                                  = "Stok Değeri";
$lang['stock_value_by_price']                         = "Stok Fiyat Değeri";
$lang['stock_value_by_cost']                          = "Stok Maliyet Değeri";
$lang['sold']                                         = "Satılan";
$lang['purchased']                                    = "Satın alınan";
$lang['chart_lable_toggle']                           = "Grafiği tıklayarak değiştirebilirsiniz. Göster/Gizle için herhangi bir grafiği tıklayın.";
$lang['register_report']                              = "Kayıt Raporu";
$lang['sEmptyTable']                                  = "Tabloda veri yok";
$lang['upcoming_events']                              = "Yaklaşan Etkinlikler";
$lang['clear_ls']                                     = "Kaydedilmiş tüm yerel verileri temizle";
$lang['clear']                                        = "Temizle";
$lang['edit_order_discount']                          = "Sipariş İndirimi Düzenle";
$lang['product_variant']                              = "Ürün Varyasyon";
$lang['product_variants']                             = "Ürün Varyasyonları";
$lang['prduct_not_found']                             = "Ürün bulunamadı";
$lang['list_open_registers']                          = "Açık Kayıt Listesi";
$lang['delivery']                                     = "Teslimat";
$lang['serial_no']                                    = "Seri/Imei Numarası";
$lang['logo']                                         = "Logo";
$lang['attachment']                                   = "Eklenti";
$lang['balance']                                      = "Bakiye";
$lang['nothing_found']                                = "Eşleşen sonuç bulunamadı";
$lang['db_restored']                                  = "Veritabanı başarıyla restore edildi.";
$lang['backups']                                      = "Yedekler";
$lang['chart']                                        = "Grafik";
$lang['received']                                     = "Alınan";
$lang['returned']                                     = "İade";
$lang['award_points']                                 = 'Ödül Puanları';
$lang['expenses']                                     = "Giderler";
$lang['add_expense']                                  = "Gider Ekle";
$lang['other']                                        = "Diğer";
$lang['none']                                         = "Yok";
$lang['calculator']                                   = "Hesap Makinesi";
$lang['updates']                                      = "Güncellemeler";
$lang['update_available']                             = "Yeni güncelleme Mevcut, lütfen şimdi güncelleyin .";
$lang['please_select_customer_warehouse']             = "Müşteri / depo seçiniz";
$lang['variants']                                     = "Varyasyonlar";
$lang['add_sale_by_csv']                              = "CSV ile Satış Ekle";
$lang['categories_report']                            = "Kategori Raporları";
$lang['adjust_quantity']                              = "Miktar Ayarla";
$lang['quantity_adjustments']                         = "Miktar Ayarlamaları";
$lang['partial']                                      = "Kısmi";
$lang['unexpected_value']                             = "Beklenmeyen Değer!";
$lang['select_above']                                 = "İlk yukarıda seçiniz";
$lang['no_user_selected']                             = "Hiç kullanıcı seçilmedi, lütfen en az bir kullanıcı seçiniz";
$lang['due']                                          = "Vadesi Dolmuş";
$lang['ordered']                                      = "Sipariş Edilen";
$lang['profit']                                       = "Kâr";
$lang['unit_and_net_tip']                             = "Tüm satışlar için birim üzerinden hesaplanan (vergi hariş) ve net hesaplanan (vergi dahil)";
$lang['expiry_alerts']                                = "Geçerlilik Uyarısı";
$lang['quantity_alerts']                              = "Miktar Uyarısı";
$lang['products_sale']                                = "Ürün Gelirleri";
$lang['products_cost']                                = "Ürün Maliyetleri";
$lang['day_profit']                                   = "Gün Kâr ve Zararlar";
$lang['get_day_profit']                               = "Günlük kâr zarar raporu almak için tarihe tıklayabilirsiniz.";
$lang['combine_to_pdf']                               = "pdf birleştirin";
$lang['print_barcode_label']                          = "Barkod / Etiket Yazdır";
$lang['list_gift_cards']                              = "Hediye Kartları Listesi";
$lang['today_profit']                                 = "Bugünün Kârı";
$lang['adjustments']                                  = "Ayarlamalar";
$lang['download_xls']                                 = "XLS olarak İndir";
$lang['browse']                                       = "Gözat ...";
$lang['transferring']                                 = "Aktarılıyor";
$lang['supplier_part_no']                             = "Tedarikçi Parça Numarası";
$lang['deposit']                                      = "Depozito";
$lang['ppp']                                          = "Paypal Pro";
$lang['stripe']                                       = "Stripe";
$lang['amount_greater_than_deposit']                  = "Tutar müşteri depozitinden büyüktür, lütfen müşteri depoziti ile aynı veya daha düşük değer girin.";
$lang['stamp_sign']                                   = "Kaşe &amp; İmza";
$lang['product_option']                               = "Ürün Varyantı";
$lang['Cheque']                                       = "Çek";
$lang['sale_reference']                               = "Satış Referans";
$lang['surcharges']                                   = "Ek ücretler";
$lang['please_wait']                                  = "Lütfen bekleyin...";
$lang['list_expenses']                                = "Giderleri Listele";
$lang['deposit']                                      = "Depozito";
$lang['deposit_amount']                               = "Depozito Tutarı";
$lang['return_purchases']                             = "İade Satın Almalar";
$lang['list_return_purchases']                        = "İade Satın Almaların Listesi";
$lang['expense_categories']                           = "Gider Kategorileri";
$lang['authorize']                                    = "Authorize.net";
$lang['expenses_report']                              = "Gider Raporu";
$lang['expense_categories']                           = "Gider Kategorileri";
$lang['edit_event']                                   = "Etkinlik Düzenle";
$lang['title']                                        = "Başlık";
$lang['event_error']                                  = "Başlık & Başlangıç gereklidir";
$lang['start']                                        = "Başlangış";
$lang['end']                                          = "Bitiş";
$lang['event_added']                                  = "Etkinlik başarıyla eklendi";
$lang['event_updated']                                = "Etkinlik başarıyla güncellendi";
$lang['event_deleted']                                = "Etkinlik başarıyla silindi";
$lang['event_color']                                  = "Etkinlik Rengi";
$lang['toggle_alignment']                             = "Geçiş Hizalama";
$lang['images_location_tip']                          = "Görseller <strong>uploads</strong> klasörüne yüklenir.";
$lang['this_sale']                                    = "Bu Satış";
$lang['return_ref']                                   = "İade Referansı";
$lang['return_total']                                 = "Toplam İade";
$lang['daily_purchases']                              = "Günlük Alımlar";
$lang['monthly_purchases']                            = "Aylık Alımlar";
$lang['reference']                                    = "Referans";
$lang['no_subcategory']                               = "Alt kategori yok";
$lang['returned_items']                               = "İade Edilen Öğeler";
$lang['return_payments']                              = "İade edilen Ödemeler";
$lang['units']                                        = "Birimler";
$lang['price_group']                                  = "Fiyat Grubu";
$lang['price_groups']                                 = "Fiyat Grupları";
$lang['no_record_selected']                           = "Kayıt seçilmedi, lütfen en az bir kayıt seçiniz";
$lang['brand']                                        = "Marka";
$lang['brands']                                       = "Markalar";
$lang['file_x_exist']                                 = "Sistem belirtilen dosyayı bulamıyor, sunucudan silinmiş veya adı değiştirilmiş olabilir.";
$lang['status_updated']                               = "Durum başarıyla güncellendi";
$lang['x_col_required']                               = "İlk %d sütun gereklidir diğerleri isteğe bağlıdır.";
$lang['brands_report']                                = "Marka Raporları";
$lang['add_adjustment']                               = "Ayarlama Ekle";
$lang['best_sellers']                                 = "Çok Satanlar";
$lang['adjustments_report']                           = "Ayarlamalar Raporu";
$lang['stock_counts']                                 = "Stok Sayımları";
$lang['count_stock']                                  = "Stok Sayısı";
$lang['download']                                     = "İndir";

$lang['please_select_these_before_adding_product']    = "Herhangi bir ürün eklemeden bunu seçiniz";
$lang['promotion_news']                               = "Promosyon Haberleri";
$lang['official_partner']                             = "Resmi Ortak";

$lang['units']                                        = "Birimler";
$lang['product_bonus']                                = "Ürün Bonusu";
$lang['multiple_discount']                            = "Çoklu indirim";
$lang['shipping_charges']                             = "Kargo Ücretleri";
$lang['points']                                       = "Mkas";
$lang['gross_price']                                  = "Brüt fiyat";
$lang['brand']                                        = "Marka";
$lang['brands']                                       = "Markalar";
$lang['bank']                                         = "Banka";

$lang['stock_card_report']                            = "Stok Kartı Raporu";

$lang['count_stock']                                  = "Stok sayımı";
$lang['consignments']                                 = "Consignments";
$lang['add_consignment']                              = "Konsinye Ekleme";
$lang['stock_counts_menu']                            = "Stok Sayım Menüsü";
$lang['browse']                                       = "Araştır";
$lang['site_name']                                    = "Site adı";
$lang['check_promo']                                  = "Promosyonu Kontrol Et";

$lang['pending']                                      = "kadar";
$lang['completed']                                    = "Tamamlanan";
$lang['canceled']                                     = "İptal edildi";
$lang['completed_min_stock']                          = "Tamamlandı (Stoku Düşürecek)";
$lang['confirmed_not_min_stock']                      = "Onaylandı (Stoku Azaltmaz)";

$lang['piutang_report']                               = "Alacak hesapları";
$lang['standard_sale']                                = "Standart satış";
$lang['aksestoko_sale']                               = "Aksestoko Satış";

$lang['semua']                                        = "Olgunluk";
$lang['h15']                                          = "Vade < 15 gün";
$lang['h1530']                                        = "Vade 15 - 30 gün";
$lang['h30']                                          = "Vade > 30 gün";
$lang['total_jt']                                     = "Toplam Vade";
$lang['total_h15']                                    = "Toplam Vade <15 gün";
$lang['total_h1530']                                  = "Toplam Vade 15 - 30 gün";
$lang['total_h30']                                    = "Toplam Vade <30 gün";

$lang['date_now']                                     = "Mevcut Tarih";
$lang['top']                                          = "Uzun Süre TOP";
$lang['tanggal_jt']                                   = "Son Tarih";
$lang['sejak']                                        = "beri";

$lang['remaining_credit_limit']                       = "Kalan Kredi Limiti";
$lang['notif_credit_limit']                           = "Satışlarınız kredi limitini aşıyor <br> Satışa devam edeceğinizden emin misiniz?";

$lang['Confirmation_Delivery']                        = "Onay Teslimatı";
$lang['delivering']                                   = "Teslimat";

$lang['tonase']                                       = "tonaj";
$lang['tonase_sale']                                  = "Satış Tonaj";
$lang['tonase_purchase']                              = "Tonaj Satın Al";
$lang['rupiah']                                       = "rupiahı";

// Menu
$lang['searching_menu']                               = "Arama Menüsü";
$lang['add_sales_person']                             = "Satış Kişi Ekle";
$lang['add_quotes']                                   = "Ekleme Alıntılar";
$lang['shipment_price_groups']                        = "Sevk Fiyatı Grupları";
$lang['list_promotion']                               = "Liste Promosyon";
$lang['transfers_add']                                = "Ekleme Transferler";
$lang['list_user_aksestoko']                          = "Liste Kullanıcı Aksestoko";
$lang['deliveries_report']                            = "Teslimatlar Raporu";
$lang['sales_booking']                                = "Satış Rezervasyon";
$lang['sales_person']                                 = "Satis elemani";

$lang['products_warehouse']                           = "Ürünler Depo  ";
$lang['quantum_sale_by_date']                         = "Tarihe Göre Kuantum Satış ";
$lang['quantum_purchase_by_date']                     = "Kuantum Alım By tarihi  ";
$lang['products_warehouse_by_date']                   = "Ürünler Depo tarafından tarihi  ";
$lang['sale_transaction']                             = "satış İşlem ";
$lang['sale_delivered']                               = "satış teslim  ";
$lang['promotion_report']                             = "Promosyon Raporu  ";
$lang['audittrail']                                   = "Denetim izi ";
$lang['user_aktivasi_aksestoko']                      = "Aktivasyon Kullanıcı Aksestoko  ";
$lang['exported_excel_reports']                       = "Dışa Excel Raporları  ";
$lang['item_delivered']                               = "Eşya teslim edildi  ";
$lang['history_login']                                = "Tarih Girişi  ";
$lang['pilih_product']                                = "Ürünü seç ";
$lang['all_brand']                                    = "Tüm Marka ";
$lang['change_filter']                                = "Değişim Filtre  ";
$lang['persebaran']                                   = "Mağaza Dağıtım  ";
$lang['quantum_penjulan']                             = "kuantum Satış ";
$lang['peta_indonesia']                               = "Endonezya Harita  ";
$lang['item_adjusment_duplicated']                    = 'Yinelenen Ayarlama Öğeler';
$lang['synchron']                                     = 'Eşitleme';

$lang['whats_new']                                    = 'Ne var ne yok?';
$lang['im_understand']                                = 'Ben anlayın değilim';
$lang['bugfix']                                       = 'Hata düzeltme';
$lang['new_feature']                                  = 'Yeni özellik';
$lang['enhancement']                                  = 'Artırma';
$lang['other']                                        = 'Diğer';

$lang['introduction'] = 'Forca-POS kullandığınız için teşekkür ederiz. Biz bu kısa anket aracılığıyla kalite ve iyi hizmet artırabilir böylece, görüş ve deneyimlerini duymak isteriz. takdir tecellisi olarak diyebiliriz teşekkür ederim.';
$lang['info_survey'] = 'Bu anketin doldurduktan, bu bildirim dissapear yapmak için bu sayfayı yeniden deneyin.';
$lang['start_survey'] = 'Başlangıç ​​Anketi';
$lang['customer_survey'] = 'Müşteri Anketi';
$lang['already_taken'] = 'Zaten bu anketi doldurdu.';

$lang['show_grafik_chart']                            = 'Durum Grafiğini Göster';
$lang['hide_grafik_chart']                            = 'Durum Grafiğini Gizle';
$lang['show_map_chart']                               = 'Harita Grafiğini Göster';
$lang['hide_map_chart']                               = 'Harita Grafiğini Gizle';
$lang['info_chart_principal']                         = '<center>Grafiği görmek için lütfen sağ üst köşedeki göz simgesini tıklayın</center>';

$lang['unassigned'] = 'Atanmayanlar';