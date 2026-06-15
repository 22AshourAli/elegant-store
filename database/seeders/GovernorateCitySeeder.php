<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Governorate;
use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class GovernorateCitySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'القاهرة' => [
                'base_cost' => 35,
                'cities' => [
                    ['name' => 'المعادي', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'مدينة نصر', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'مصر الجديدة', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'التجمع الخامس', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'الرحاب', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'الشروق', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'المقطم', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'الزمالك', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'وسط البلد', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'شبرا', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'العباسية', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'المطرية', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'عين شمس', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'حلوان', 'delivery_time' => '1-3 أيام'],
                    ['name' => '15 مايو', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'الجيزة' => [
                'base_cost' => 35,
                'cities' => [
                    ['name' => 'الدقي', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'المهندسين', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'العجوزة', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'الهرم', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'فيصل', 'delivery_time' => '1-2 يوم'],
                    ['name' => '6 أكتوبر', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الشيخ زايد', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أكتوبر الجديدة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الباويطي', 'delivery_time' => '2-4 أيام'],
                ],
            ],
            'الإسكندرية' => [
                'base_cost' => 40,
                'cities' => [
                    ['name' => 'وسط المدينة', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'سيدي بشر', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'محرم بك', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'المنتزه', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'العامرية', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'برج العرب', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'الدقهلية' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'المنصورة', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'ميت غمر', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'السنبلاوين', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'دكرنس', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'بلقاس', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'نبروه', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'طلخا', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'الشرقية' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'الزقازيق', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'بلبيس', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'العاشر من رمضان', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبو كبير', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'منيا القمح', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'فاقوس', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'كفر صقر', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'البحيرة' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'دمنهور', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'كفر الدوار', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'رشيد', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'إدكو', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبو المطامير', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'وادي النطرون', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'المحمودية', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'الغربية' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'طنطا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'المحلة الكبرى', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'كفر الزيات', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'بسيون', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'قطور', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'السنطة', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'المنوفية' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'شبين الكوم', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'منوف', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أشمون', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الباجور', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الشهداء', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'تلا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'قويسنا', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'القليوبية' => [
                'base_cost' => 40,
                'cities' => [
                    ['name' => 'بنها', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'شبرا الخيمة', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'قليوب', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'الخانكة', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'كفر شكر', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'طوخ', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'القناطر الخيرية', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'الفيوم' => [
                'base_cost' => 50,
                'cities' => [
                    ['name' => 'الفيوم', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'سنورس', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'طامية', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'إطسا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبشواي', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'يوسف الصديق', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'بني سويف' => [
                'base_cost' => 50,
                'cities' => [
                    ['name' => 'بني سويف', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الواسطى', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'ناصر', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أهناسيا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'ببا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الفشن', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'سمسطا', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'المنيا' => [
                'base_cost' => 55,
                'cities' => [
                    ['name' => 'المنيا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'ملوي', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبو قرقاص', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'سمالوط', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'مطاي', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'مغاغة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'دير مواس', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'بني مزار', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'أسيوط' => [
                'base_cost' => 55,
                'cities' => [
                    ['name' => 'أسيوط', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'ديروط', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'منفلوط', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبنوب', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبو تيج', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الغنايم', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'ساحل سليم', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'صدفا', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'سوهاج' => [
                'base_cost' => 55,
                'cities' => [
                    ['name' => 'سوهاج', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أخميم', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'طهطا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'جرجا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'البلينا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'دار السلام', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'ساقلتة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'المراغة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'جهينة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'العسيرات', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'قنا' => [
                'base_cost' => 60,
                'cities' => [
                    ['name' => 'قنا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'نجع حمادي', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'دشنا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'فرشوط', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبو تشت', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'قفط', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'نقادة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الوقف', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'الأقصر' => [
                'base_cost' => 60,
                'cities' => [
                    ['name' => 'الأقصر', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'البياضية', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'القرنة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أرمنت', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'إسنا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الزينية', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الطيبة', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'أسوان' => [
                'base_cost' => 65,
                'cities' => [
                    ['name' => 'أسوان', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'دراو', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'كوم أمبو', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'نصر النوبة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'إدفو', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'السباعية', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبو سمبل', 'delivery_time' => '2-4 أيام'],
                ],
            ],
            'البحر الأحمر' => [
                'base_cost' => 70,
                'cities' => [
                    ['name' => 'الغردقة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'مرسى علم', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'سفاجا', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'القصير', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'رأس غارب', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'الشلاتين', 'delivery_time' => '3-5 أيام'],
                    ['name' => 'حلايب', 'delivery_time' => '3-5 أيام'],
                ],
            ],
            'الوادي الجديد' => [
                'base_cost' => 75,
                'cities' => [
                    ['name' => 'الخارجة', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'الداخلة', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'باريس', 'delivery_time' => '3-5 أيام'],
                    ['name' => 'بلاط', 'delivery_time' => '3-5 أيام'],
                    ['name' => 'الفرافرة', 'delivery_time' => '3-5 أيام'],
                ],
            ],
            'مطروح' => [
                'base_cost' => 70,
                'cities' => [
                    ['name' => 'مرسى مطروح', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الحمام', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'العلمين', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الضبعة', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'سيوة', 'delivery_time' => '3-5 أيام'],
                    ['name' => 'النجيلة', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'سيدي براني', 'delivery_time' => '3-5 أيام'],
                    ['name' => 'السلوم', 'delivery_time' => '3-5 أيام'],
                ],
            ],
            'شمال سيناء' => [
                'base_cost' => 75,
                'cities' => [
                    ['name' => 'العريش', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'بئر العبد', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'الشيخ زويد', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'رفح', 'delivery_time' => '2-4 أيام'],
                ],
            ],
            'جنوب سيناء' => [
                'base_cost' => 75,
                'cities' => [
                    ['name' => 'شرم الشيخ', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'دهب', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'نويبع', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'طور سيناء', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'سانت كاترين', 'delivery_time' => '2-4 أيام'],
                    ['name' => 'طابا', 'delivery_time' => '2-4 أيام'],
                ],
            ],
            'دمياط' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'دمياط', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'رأس البر', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'فارسكور', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'كفر سعد', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الزرقا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'كفر البطيخ', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'بورسعيد' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'بورسعيد', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'بورفؤاد', 'delivery_time' => '1-2 يوم'],
                ],
            ],
            'الإسماعيلية' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'الإسماعيلية', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'فايد', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'التل الكبير', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'أبو صوير', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'القنطرة', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'السويس' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'السويس', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'عتاقة', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'فيصل', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'الأربعين', 'delivery_time' => '1-2 يوم'],
                    ['name' => 'الجناين', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            'كفر الشيخ' => [
                'base_cost' => 45,
                'cities' => [
                    ['name' => 'كفر الشيخ', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'دسوق', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'بيلا', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'فوه', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'مطوبس', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الحامول', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'الرياض', 'delivery_time' => '1-3 أيام'],
                    ['name' => 'سيدي سالم', 'delivery_time' => '1-3 أيام'],
                ],
            ],
            // الأقصر already defined above with its cities
        ];

        foreach ($data as $govName => $govData) {
            $gov = Governorate::firstOrCreate(
                ['name' => $govName],
                [
                    'is_active' => true,
                    'base_shipping_cost' => $govData['base_cost'],
                ]
            );

            foreach ($govData['cities'] ?? [] as $cityData) {
                $city = City::firstOrCreate(
                    ['governorate_id' => $gov->id, 'name' => $cityData['name']],
                    [
                        'delivery_time' => $cityData['delivery_time'] ?? '1-3 أيام',
                        'is_active' => true,
                    ]
                );

                if (isset($cityData['min_cart_amount'])) {
                    ShippingRate::firstOrCreate(
                        ['governorate_id' => $gov->id, 'city_id' => $city->id],
                        [
                            'min_cart_amount' => $cityData['min_cart_amount'],
                            'rate' => 0,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
