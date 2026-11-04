<?php
/**
 * Шаблон для карточки врача в списке специалистов
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

if( !empty($args['id']) ) {
    $doctor_id = $args['id'];
}
else {
    $doctor_id = get_the_ID();
}

$image = get_field('img', $doctor_id);
$specialization = get_field('spec_filter', $doctor_id);
$specialist_type = get_field('specialist_type');
?>

<?php
// Данные для сортировки каталога (catalogSort() в assets/js/script.js):
// data-nearest — время ближайшего окна (0 — окон нет), data-price — цена первичного приёма числом
$sort_cost    = get_field('cost_1', $doctor_id);
$sort_price   = (int) preg_replace('/\D/', '', (string) $sort_cost);
$sort_nearest = function_exists('clinic_get_doctor_nearest_ts') ? (int) clinic_get_doctor_nearest_ts($doctor_id) : 0;
?>
<div class="specItem" data-nearest="<?= $sort_nearest; ?>" data-price="<?= $sort_price; ?>">
    <?php if( $image ) { ?>
    <a class="specItemImg" href="<?= get_permalink($doctor_id); ?>">
        <img src="<?= $image['url']; ?>" alt="<?= $image['alt']; ?>">
        <?php the_doctor_experience($doctor_id); ?>
        </a>
    <?php } ?>
    </a>
    <div class="specItemInfoWrap">
        <?php if( $specialist_type == 'psychologist' ): ?>
            <div class="specItemOnlineLabel">
                <img src="<?= THEME_URI; ?>/assets/img/ico/ico_ps.svg" alt="<?= $image['alt']; ?>">
                Психолог
            </div>
        <?php elseif( $specialist_type == 'clinical' ): ?>
            <div class="specItemOnlineLabel">
                <img src="<?= THEME_URI; ?>/assets/img/ico/ico_cl.svg" alt="<?= $image['alt']; ?>">
                Клинический психолог
            </div>
        <?php endif; ?>
        <div class="specItemInfo">
            <a class="specItemTitle" href="<?= get_permalink($doctor_id); ?>"><?= get_the_title($doctor_id); ?></a>
            <a class="specItemText" href="<?= get_permalink($doctor_id); ?>"><?= get_the_excerpt($doctor_id); ?></a>
            <?php if (!is_wp_error($specialization) && !empty($specialization)) { ?>
            <div class="specItemFeatures">
                <?php foreach ($specialization as $spec) {
                    if (function_exists('clinic_is_intern_specialty_term') && clinic_is_intern_specialty_term($spec)) {
                        continue;
                    }
                    $color = get_field('color', $spec->taxonomy . '_' . $spec->term_id);
                ?>
                <div class="specItemFeature<?= !empty($color) ? ' color' . $color : ''; ?>"><?= esc_html($spec->name); ?></div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php
            $cost = get_field('cost_1', $doctor_id);
            // Ближайшее окно из МИС (inc/helpers/ajax.php): timestamp; 0 — окон нет; null — МИС сейчас недоступна
            $nearest_ts = function_exists('clinic_get_doctor_nearest_ts') ? clinic_get_doctor_nearest_ts($doctor_id) : null;
            ?>
            <?php if( !empty($cost) || $nearest_ts !== null ) { ?>
            <div class="specItemBooking">
                <?php if( $nearest_ts > 0 ) { ?>
                <div class="specItemNearest">
                    <svg class="specItemNearestIco" xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M5 0C5.55228 0 6 0.447715 6 1V2H15V1C15 0.447715 15.4477 0 16 0C16.5523 0 17 0.447715 17 1V2C18.6569 2 20 3.34315 20 5V19C20 20.6569 18.6569 22 17 22H3C1.34315 22 0 20.6569 0 19V5C0 3.34315 1.34315 2 3 2H4V1C4 0.447715 4.44772 0 5 0ZM4 4H3C2.44772 4 2 4.44772 2 5V19C2 19.5523 2.44772 20 3 20H17C17.5523 20 18 19.5523 18 19V5C18 4.44772 17.5523 4 17 4V5C17 5.55228 16.5523 6 16 6C15.4477 6 15 5.55228 15 5V4H6V5C6 5.55228 5.55228 6 5 6C4.44772 6 4 5.55228 4 5V4ZM3 8C3 7.44772 3.44772 7 4 7L16 7C16.5523 7 17 7.44772 17 8C17 8.55229 16.5523 9 16 9L4 9C3.44772 9 3 8.55228 3 8ZM3 13C3 12.4477 3.44772 12 4 12H6C6.55228 12 7 12.4477 7 13C7 13.5523 6.55228 14 6 14H4C3.44772 14 3 13.5523 3 13ZM8 13C8 12.4477 8.44772 12 9 12H11C11.5523 12 12 12.4477 12 13C12 13.5523 11.5523 14 11 14H9C8.44772 14 8 13.5523 8 13ZM13 13C13 12.4477 13.4477 12 14 12H16C16.5523 12 17 12.4477 17 13C17 13.5523 16.5523 14 16 14H14C13.4477 14 13 13.5523 13 13ZM3 16C3 15.4477 3.44772 15 4 15H6C6.55228 15 7 15.4477 7 16C7 16.5523 6.55228 17 6 17H4C3.44772 17 3 16.5523 3 16ZM8 16C8 15.4477 8.44772 15 9 15H11C11.5523 15 12 15.4477 12 16C12 16.5523 11.5523 17 11 17H9C8.44772 17 8 16.5523 8 16ZM13 16C13 15.4477 13.4477 15 14 15H16C16.5523 15 17 15.4477 17 16C17 16.5523 16.5523 17 16 17H14C13.4477 17 13 16.5523 13 16Z"></path>
                    </svg>
                    <div class="specItemNearestInfo">
                        <span class="specItemNearestLabel">Ближайшее окно</span>
                        <span class="specItemNearestDate"><?= esc_html(clinic_format_nearest_date($nearest_ts)); ?></span>
                    </div>
                </div>
                <?php } elseif( $nearest_ts === 0 ) { ?>
                <div class="specItemWaiting">Запись в лист ожидания</div>
                <?php } ?>
                <?php if( !empty($cost) ) { ?>
                <div class="specItemPrice"><?= $cost; ?> ₽</div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </div>
    
</div>
