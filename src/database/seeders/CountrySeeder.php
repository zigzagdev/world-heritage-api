<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use RuntimeException;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countriesIso2 = [
            ['AF','Afghanistan'], ['AL','Albania'], ['DZ','Algeria'], ['AD','Andorra'], ['AO','Angola'],
            ['AG','Antigua and Barbuda'], ['AR','Argentina'], ['AM','Armenia'], ['AU','Australia'], ['AT','Austria'],
            ['AZ','Azerbaijan'], ['BS','Bahamas'], ['BH','Bahrain'], ['BD','Bangladesh'], ['BB','Barbados'],
            ['BY','Belarus'], ['BE','Belgium'], ['BZ','Belize'], ['BJ','Benin'], ['BT','Bhutan'],
            ['BO','Bolivia'], ['BA','Bosnia and Herzegovina'], ['BW','Botswana'], ['BR','Brazil'], ['BN','Brunei Darussalam'],
            ['BG','Bulgaria'], ['BF','Burkina Faso'], ['BI','Burundi'], ['CV','Cabo Verde'], ['KH','Cambodia'],
            ['CM','Cameroon'], ['CA','Canada'], ['CF','Central African Republic'], ['TD','Chad'], ['CL','Chile'],
            ['CN','China'], ['CO','Colombia'], ['KM','Comoros'], ['CG','Congo'], ['CD','Congo, Democratic Republic of the'],
            ['CR','Costa Rica'], ['CI',"Cote d'Ivoire"], ['HR','Croatia'], ['CU','Cuba'], ['CY','Cyprus'],
            ['CZ','Czechia'], ['DK','Denmark'], ['DJ','Djibouti'], ['DM','Dominica'], ['DO','Dominican Republic'],
            ['EC','Ecuador'], ['EG','Egypt'], ['SV','El Salvador'], ['GQ','Equatorial Guinea'], ['ER','Eritrea'],
            ['EE','Estonia'], ['SZ','Eswatini'], ['ET','Ethiopia'], ['FJ','Fiji'], ['FI','Finland'],
            ['FR','France'], ['GA','Gabon'], ['GM','Gambia'], ['GE','Georgia'], ['DE','Germany'],
            ['GH','Ghana'], ['GR','Greece'], ['GD','Grenada'], ['GT','Guatemala'], ['GN','Guinea'],
            ['GW','Guinea-Bissau'], ['GY','Guyana'], ['HT','Haiti'], ['HN','Honduras'], ['HU','Hungary'],
            ['IS','Iceland'], ['IN','India'], ['ID','Indonesia'], ['IR','Iran'], ['IQ','Iraq'],
            ['IE','Ireland'], ['IL','Israel'], ['IT','Italy'], ['JM','Jamaica'], ['JP','Japan'],
            ['JO','Jordan'], ['KZ','Kazakhstan'], ['KE','Kenya'], ['KI','Kiribati'], ['KP',"Korea, Democratic People's Republic of"],
            ['KR','Korea, Republic of'], ['KW','Kuwait'], ['KG','Kyrgyzstan'], ['LA',"Lao People's Democratic Republic"], ['LV','Latvia'],
            ['LB','Lebanon'], ['LS','Lesotho'], ['LR','Liberia'], ['LY','Libya'], ['LI','Liechtenstein'],
            ['LT','Lithuania'], ['LU','Luxembourg'], ['MG','Madagascar'], ['MW','Malawi'], ['MY','Malaysia'],
            ['MV','Maldives'], ['ML','Mali'], ['MT','Malta'], ['MH','Marshall Islands'], ['MR','Mauritania'],
            ['MU','Mauritius'], ['MX','Mexico'], ['FM','Micronesia, Federated States of'], ['MD','Moldova'], ['MC','Monaco'],
            ['MN','Mongolia'], ['ME','Montenegro'], ['MA','Morocco'], ['MZ','Mozambique'], ['MM','Myanmar'],
            ['NA','Namibia'], ['NR','Nauru'], ['NP','Nepal'], ['NL','Netherlands'], ['NZ','New Zealand'],
            ['NI','Nicaragua'], ['NE','Niger'], ['NG','Nigeria'], ['MK','North Macedonia'], ['NO','Norway'],
            ['OM','Oman'], ['PK','Pakistan'], ['PW','Palau'], ['PA','Panama'], ['PG','Papua New Guinea'],
            ['PY','Paraguay'], ['PE','Peru'], ['PH','Philippines'], ['PL','Poland'], ['PT','Portugal'],
            ['QA','Qatar'], ['RO','Romania'], ['RU','Russian Federation'], ['RW','Rwanda'], ['KN','Saint Kitts and Nevis'],
            ['LC','Saint Lucia'], ['VC','Saint Vincent and the Grenadines'], ['WS','Samoa'], ['SM','San Marino'], ['ST','Sao Tome and Principe'],
            ['SA','Saudi Arabia'], ['SN','Senegal'], ['RS','Serbia'], ['SC','Seychelles'], ['SL','Sierra Leone'],
            ['SG','Singapore'], ['SK','Slovakia'], ['SI','Slovenia'], ['SB','Solomon Islands'], ['SO','Somalia'],
            ['ZA','South Africa'], ['SS','South Sudan'], ['ES','Spain'], ['LK','Sri Lanka'], ['SD','Sudan'],
            ['SR','Suriname'], ['SE','Sweden'], ['CH','Switzerland'], ['SY','Syrian Arab Republic'], ['TJ','Tajikistan'],
            ['TH','Thailand'], ['TL','Timor-Leste'], ['TG','Togo'], ['TO','Tonga'], ['TT','Trinidad and Tobago'],
            ['TN','Tunisia'], ['TR','Türkiye'], ['TM','Turkmenistan'], ['TV','Tuvalu'], ['UG','Uganda'],
            ['UA','Ukraine'], ['AE','United Arab Emirates'], ['GB','United Kingdom of Great Britain and Northern Ireland'],
            ['TZ','Tanzania, United Republic of'], ['US','United States of America'], ['UY','Uruguay'], ['UZ','Uzbekistan'],
            ['VU','Vanuatu'], ['VE','Venezuela'], ['VN','Viet Nam'], ['YE','Yemen'], ['ZM','Zambia'], ['ZW','Zimbabwe'],
            ['VA','Holy See'], ['PS','State of Palestine'],
        ];

        $iso2to3 = [
            'AF'=>'AFG','AL'=>'ALB','DZ'=>'DZA','AD'=>'AND','AO'=>'AGO','AG'=>'ATG','AR'=>'ARG','AM'=>'ARM','AU'=>'AUS','AT'=>'AUT',
            'AZ'=>'AZE','BS'=>'BHS','BH'=>'BHR','BD'=>'BGD','BB'=>'BRB','BY'=>'BLR','BE'=>'BEL','BZ'=>'BLZ','BJ'=>'BEN','BT'=>'BTN',
            'BO'=>'BOL','BA'=>'BIH','BW'=>'BWA','BR'=>'BRA','BN'=>'BRN','BG'=>'BGR','BF'=>'BFA','BI'=>'BDI','CV'=>'CPV','KH'=>'KHM',
            'CM'=>'CMR','CA'=>'CAN','CF'=>'CAF','TD'=>'TCD','CL'=>'CHL','CN'=>'CHN','CO'=>'COL','KM'=>'COM','CG'=>'COG','CD'=>'COD',
            'CR'=>'CRI','CI'=>'CIV','HR'=>'HRV','CU'=>'CUB','CY'=>'CYP','CZ'=>'CZE','DK'=>'DNK','DJ'=>'DJI','DM'=>'DMA','DO'=>'DOM',
            'EC'=>'ECU','EG'=>'EGY','SV'=>'SLV','GQ'=>'GNQ','ER'=>'ERI','EE'=>'EST','SZ'=>'SWZ','ET'=>'ETH','FJ'=>'FJI','FI'=>'FIN',
            'FR'=>'FRA','GA'=>'GAB','GM'=>'GMB','GE'=>'GEO','DE'=>'DEU','GH'=>'GHA','GR'=>'GRC','GD'=>'GRD','GT'=>'GTM','GN'=>'GIN',
            'GW'=>'GNB','GY'=>'GUY','HT'=>'HTI','HN'=>'HND','HU'=>'HUN','IS'=>'ISL','IN'=>'IND','ID'=>'IDN','IR'=>'IRN','IQ'=>'IRQ',
            'IE'=>'IRL','IL'=>'ISR','IT'=>'ITA','JM'=>'JAM','JP'=>'JPN','JO'=>'JOR','KZ'=>'KAZ','KE'=>'KEN','KI'=>'KIR','KP'=>'PRK',
            'KR'=>'KOR','KW'=>'KWT','KG'=>'KGZ','LA'=>'LAO','LV'=>'LVA','LB'=>'LBN','LS'=>'LSO','LR'=>'LBR','LY'=>'LBY','LI'=>'LIE',
            'LT'=>'LTU','LU'=>'LUX','MG'=>'MDG','MW'=>'MWI','MY'=>'MYS','MV'=>'MDV','ML'=>'MLI','MT'=>'MLT','MH'=>'MHL','MR'=>'MRT',
            'MU'=>'MUS','MX'=>'MEX','FM'=>'FSM','MD'=>'MDA','MC'=>'MCO','MN'=>'MNG','ME'=>'MNE','MA'=>'MAR','MZ'=>'MOZ','MM'=>'MMR',
            'NA'=>'NAM','NR'=>'NRU','NP'=>'NPL','NL'=>'NLD','NZ'=>'NZL','NI'=>'NIC','NE'=>'NER','NG'=>'NGA','MK'=>'MKD','NO'=>'NOR',
            'OM'=>'OMN','PK'=>'PAK','PW'=>'PLW','PA'=>'PAN','PG'=>'PNG','PY'=>'PRY','PE'=>'PER','PH'=>'PHL','PL'=>'POL','PT'=>'PRT',
            'QA'=>'QAT','RO'=>'ROU','RU'=>'RUS','RW'=>'RWA','KN'=>'KNA','LC'=>'LCA','VC'=>'VCT','WS'=>'WSM','SM'=>'SMR','ST'=>'STP',
            'SA'=>'SAU','SN'=>'SEN','RS'=>'SRB','SC'=>'SYC','SL'=>'SLE','SG'=>'SGP','SK'=>'SVK','SI'=>'SVN','SB'=>'SLB','SO'=>'SOM',
            'ZA'=>'ZAF','SS'=>'SSD','ES'=>'ESP','LK'=>'LKA','SD'=>'SDN','SR'=>'SUR','SE'=>'SWE','CH'=>'CHE','SY'=>'SYR','TJ'=>'TJK',
            'TH'=>'THA','TL'=>'TLS','TG'=>'TGO','TO'=>'TON','TT'=>'TTO','TN'=>'TUN','TR'=>'TUR','TM'=>'TKM','TV'=>'TUV','UG'=>'UGA',
            'UA'=>'UKR','AE'=>'ARE','GB'=>'GBR','TZ'=>'TZA','US'=>'USA','UY'=>'URY','UZ'=>'UZB','VU'=>'VUT','VE'=>'VEN','VN'=>'VNM',
            'YE'=>'YEM','ZM'=>'ZMB','ZW'=>'ZWE','VA'=>'VAT','PS'=>'PSE',
        ];

        $regionsIso2 = [
            'Africa' => [
                'DZ','AO','BJ','BF','BI','CV','CM','CF','TD','KM','CG','CD','CI','DJ','EG','GQ','ER','SZ','ET','GA','GM','GH','GN','GW',
                'KE','LS','LR','LY','MG','MW','ML','MR','MU','MA','MZ','NA','NE','NG','RW','ST','SN','SC','SL','SO','ZA','SS','SD','TZ','TG','TN','UG','ZM','ZW',
            ],
            'Americas' => [
                'AG','AR','BS','BB','BZ','BO','BR','CA','CL','CO','CR','CU','DM','DO','EC','SV','GD','GT','GY','HT','HN','JM','KN','LC','VC','MX','NI','PA','PE','PY','SR','TT','US','UY','VE',
            ],
            'Asia' => [
                'AF','AM','AZ','BH','BD','BT','BN','KH','CN','CY','GE','IN','ID','IR','IQ','IL','JO','JP','KZ','KW','KG','LA','LB','MY','MV','MN','MM','NP','KP','KR','OM','PK','PS','PH','QA','SA','SG','LK','SY','TJ','TH','TL','TM','TR','AE','UZ','VN','YE',
            ],
            'Europe' => [
                'AD','AL','AT','BY','BE','BA','BG','HR','CZ','DK','EE','FI','FR','DE','GR','HU','IS','IE','IT','LV','LI','LT','LU','MT','MD','MC','ME','NL','MK','NO','PL','PT','RO','RU','SM','RS','SK','SI','ES','SE','CH','UA','GB','VA',
            ],
            'Oceania' => [
                'AU','FJ','KI','MH','FM','NR','NZ','PW','PG','WS','SB','TO','TV','VU',
            ],
        ];

        $nameJpIso2 = [
            'JP'=>'日本','CN'=>'中国','KR'=>'大韓民国','KP'=>'朝鮮民主主義人民共和国','TW'=> '台湾',
            'SG'=>'シンガポール','MY'=>'マレーシア','TH'=>'タイ','VN'=>'ベトナム','PH'=>'フィリピン','ID'=>'インドネシア',
            'LA'=>'ラオス','KH'=>'カンボジア','MM'=>'ミャンマー','LK'=>'スリランカ','IN'=>'インド','BD'=>'バングラデシュ',
            'IR'=>'イラン','IQ'=>'イラク','IL'=>'イスラエル','JO'=>'ヨルダン','SA'=>'サウジアラビア','AE'=>'アラブ首長国連邦','QA'=>'カタール','KW'=>'クウェート',
            'TR'=>'トルコ','KZ'=>'カザフスタン','KG'=>'キルギス','TJ'=>'タジキスタン','TM'=>'トルクメニスタン','MN'=>'モンゴル','PK'=>'パキスタン','NP'=>'ネパール','PS'=>'パレスチナ',
            'AF'=>'アフガニスタン','AZ'=>'アゼルバイジャン','AM'=>'アルメニア','LB'=>'レバノン','OM'=>'オマーン','YE'=>'イエメン','BH'=>'バーレーン','MV'=>'モルディブ',
            'AL'=>'アルバニア', 'AD'=>'アンドラ','AT'=>'オーストリア','BE'=>'ベルギー','BA'=>'ボスニア・ヘルツェゴビナ','BG'=>'ブルガリア','HR'=>'クロアチア',
            'CZ'=>'チェコ','FR'=>'フランス','DE'=>'ドイツ','IT'=>'イタリア','MK'=>'北マケドニア','PL'=>'ポーランド','RO'=>'ルーマニア',
            'SK'=>'スロバキア','SI'=>'スロベニア','ES'=>'スペイン','CH'=>'スイス','UA'=>'ウクライナ','GB'=>'イギリス','IE'=>'アイルランド',
            'NL'=>'オランダ','PT'=>'ポルトガル','NO'=>'ノルウェー','SE'=>'スウェーデン','FI'=>'フィンランド','EE'=>'エストニア','LV'=>'ラトビア','LT'=>'リトアニア',
            'IS'=>'アイスランド','DK'=>'デンマーク','LI'=>'リヒテンシュタイン','LU'=>'ルクセンブルク','MT'=>'マルタ','MC'=>'モナコ','ME'=>'モンテネグロ','SM'=>'サンマリノ','VA'=>'バチカン','RU'=>'ロシア連邦','GE'=>'ジョージア','CY'=>'キプロス',
            'BY'=>'ベラルーシ','GR'=>'ギリシャ','RS'=>'セルビア',
            'AO'=>'アンゴラ','EG'=>'エジプト','MA'=>'モロッコ','TN'=>'チュニジア','DZ'=>'アルジェリア','ZA'=>'南アフリカ共和国','ET'=>'エチオピア','KE'=>'ケニア','NG'=>'ナイジェリア','GH'=>'ガーナ','SN'=>'セネガル','TZ'=>'タンザニア','UG'=>'ウガンダ','RW'=>'ルワンダ','ZM'=>'ザンビア','ZW'=>'ジンバブエ','CM'=>'カメルーン','CI'=>'コートジボワール','GA'=>'ガボン','GM'=>'ガンビア','GN'=>'ギニア','GW'=>'ギニアビサウ','BI'=>'ブルンジ','BF'=>'ブルキナファソ','BJ'=>'ベナン','CV'=>'カーボベルデ','KM'=>'コモロ','CG'=>'コンゴ共和国','CD'=>'コンゴ民主共和国','DJ'=>'ジブチ','ER'=>'エリトリア','GQ'=>'赤道ギニア','LY'=>'リビア','LR'=>'リベリア','LS'=>'レソト','MG'=>'マダガスカル','MW'=>'マラウイ','ML'=>'マリ','MR'=>'モーリタニア','MU'=>'モーリシャス','MZ'=>'モザンビーク','NA'=>'ナミビア','NE'=>'ニジェール','SC'=>'セーシェル','SL'=>'シエラレオネ','SO'=>'ソマリア','SS'=>'南スーダン','SD'=>'スーダン','TG'=>'トーゴ',
            'US'=>'アメリカ合衆国','CA'=>'カナダ','MX'=>'メキシコ','AR'=>'アルゼンチン','BR'=>'ブラジル','CL'=>'チリ','CO'=>'コロンビア','PE'=>'ペルー','BO'=>'ボリビア','PY'=>'パラグアイ','UY'=>'ウルグアイ','VE'=>'ベネズエラ','EC'=>'エクアドル','CR'=>'コスタリカ','PA'=>'パナマ','CU'=>'キューバ','DO'=>'ドミニカ共和国','DM'=>'ドミニカ国','HT'=>'ハイチ','HN'=>'ホンジュラス','NI'=>'ニカラグア','SV'=>'エルサルバドル','GT'=>'グアテマラ','GY'=>'ガイアナ','SR'=>'スリナム','BS'=>'バハマ','BB'=>'バルバドス','BZ'=>'ベリーズ','GD'=>'グレナダ','JM'=>'ジャマイカ','KN'=>'セントクリストファー・ネーヴィス','LC'=>'セントルシア','VC'=>'セントビンセントおよびグレナディーン諸島','TT'=>'トリニダード・トバゴ',
            'AU'=>'オーストラリア','NZ'=>'ニュージーランド','PG'=>'パプアニューギニア','FJ'=>'フィジー','WS'=>'サモア','SB'=>'ソロモン諸島','TO'=>'トンガ','TV'=>'ツバル','VU'=>'バヌアツ','FM'=>'ミクロネシア連邦','MH'=>'マーシャル諸島','PW'=>'パラオ','KI'=>'キリバス','NR'=>'ナウル',
        ];

        $countriesIso3 = array_map(function ($c) use ($iso2to3, $nameJpIso2) {
            [$code2, $nameEn] = $c;
            $code3 = $iso2to3[$code2] ?? null;
            if (!$code3) {
                throw new RuntimeException("ISO2 code {$code2} has no ISO3 mapping.");
            }
            return [
                'state_party_code' => $code3,
                'name_en'          => $nameEn,
                'name_jp'          => $nameJpIso2[$code2] ?? null,
            ];
        }, $countriesIso2);

        $regionByIso3 = [];
        foreach ($regionsIso2 as $regionName => $codes2) {
            foreach ($codes2 as $code2) {
                $code3 = $iso2to3[$code2] ?? null;
                if ($code3) $regionByIso3[$code3] = $regionName;
            }
        }

        $rows = array_map(function ($row) use ($regionByIso3) {
            $row['region'] = $regionByIso3[$row['state_party_code']] ?? null;
            return $row;
        }, $countriesIso3);

        Country::query()->upsert(
            $rows,
            ['state_party_code'],
            ['name_en','name_jp','region']
        );
    }
}
