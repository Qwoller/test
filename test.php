<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
Loader::includeModule("iblock");

// Функция для определения ID инфоблока (обычно находится в init)
function iblock(string $code, ?string $type = null): ?int
{
    try {
        $iblockId = Bitrix\Iblock\IblockTable::getList(['filter' => ['CODE' => $code]])->Fetch();
        return $iblockId['ID'];
    } catch (Exception $e) {
        return false;
    }
}

// Предположим что данные о дате и городе передаются с гет параметрами
$date = $_GET['date'];
$city = $_GET['city'];


// Фильтр
$filter['=ACTIVE'] = 'Y';
$filter['CITY_VALUE'] = $city;

$rs = CIBlockElement::GetList(
    [
        'SORT' => 'ASC'
    ],
    [
        'IBLOCK_ID' => iblock('events'), // получение ID_IBLOCK мероприятий
        'LOGIC' => 'AND',
        [
            '<=DATE_ACTIVE_FROM' => $date
        ],
        [
            '>=DATE_ACTIVE_TO' => $date
        ],
        $filter
    ],
    false,
    false,
    [
        'ID',
        'IBLOCK_ID',
        'NAME',
        'PREVIEW_PICTURE',
        'PROPERTY_CITY.ID', // Получение ID привязанного города
        'PROPERTY_CITY.NAME', // Получение названия привязанного города
        'DATE_ACTIVE_FROM',
        'DATE_ACTIVE_TO',
        'PROPERTY_MEMBERS.NAME', // Получение имен привязанных участников
	'PROPERTY_MEMBERS.ID' // Получение ID привязанных участников
    ]
);
while($ob = $rs->GetNext())
{
    if(!isset($arResult['ITEM'][$ob['ID']])){
        $arResult['ITEM'][$ob['ID']] = [
            'name' => $ob['NAME'],
            'id' => $ob['ID'],
            'img' => CFile::GetPath($ob['PREVIEW_PICTURE']),
            'city_id' => $ob['PROPERTY_CITY_ID'],
            'city_name' => $ob['PROPERTY_CITY_NAME'],
            'list_members' => [$ob['PROPERTY_MEMBERS_ID'] => $ob['PROPERTY_MEMBERS_NAME']]
        ];
    }
    else
    {
        $arResult['ITEM'][$ob['ID']]['list_members'][$ob['PROPERTY_MEMBERS_ID']] = $ob['PROPERTY_MEMBERS_NAME'];
    }
}

?>
