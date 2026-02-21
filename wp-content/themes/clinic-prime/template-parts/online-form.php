<?php
/**
 * Template Name: Онлайн форма
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
<section class="innerSection">
    <div class="container small">
        <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
        <h1 class="titleBig"><span><?php the_title(); ?></span></h1>
        <?php get_template_part('template-parts/parts/online-form'); ?>
    </div>
</section>
<?php endwhile; ?>


<!-- Фиксированные элементы -->

<div class="fixed-vidjet-open active">
    <div class="fixed-vidjet-close">
        <img src="<?= THEME_URI; ?>/assets/img/onlineForm/close.svg" alt="">
    </div>
    <div class="fixed-vidjet-img">
        <img src="<?= THEME_URI; ?>/assets/img/onlineForm/img2.jpg" alt="">
    </div>
    <div class="fixed-vidjet-text">
        Если в процессе возникнут вопросы, вы можете обратиться к <a href="" target="_blank">менеджеру Центра</a>
    </div>
</div>
<div class="fixed-vidjet-mini">
    <img src="<?= THEME_URI; ?>/assets/img/onlineForm/ico8.svg" alt="">
</div>

<!-- Модальное окно успешной брони -->
<div id="bookingSuccessModal" class="result-modal">
    <div class="result-modal-content">
        <button id="closeBookingModal" class="but calendar-button">
            <img src="<?= THEME_URI; ?>/assets/img/onlineForm/close.svg" alt="">
        </button>
        <div class="result-modal-header">
            <div class="result-modal-ico"><img src="<?= THEME_URI; ?>/assets/img/onlineForm/ico7.svg" alt=""></div>
            <h3 id="successModalTitle">
                Ваша консультация<br>
                забронирована!
            </h3>
        </div>
        <div class="result-modal-body">
            <div class="result-modal-details">
                <div class="result-modal-detail">
                    <span class="result-modal-detail-label">Дата и время:</span>
                    <span class="result-modal-detail-value" id="successDateTime"></span>
                </div>
                <div class="result-modal-detail">
                    <span class="result-modal-detail-label">Специалист:</span>
                    <span class="result-modal-detail-value" id="successSpecialistName"></span>
                </div>
            </div>
            <div class="waiting-list-message">
                📧 Специалист направит вам ссылку на видеозвонок за час до встречи.
            </div>
            <div class="result-modal-but">
                <a href="" target="_blank" class="but but-calendar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7 1C7.55228 1 8 1.44772 8 2V3H17V2C17 1.44772 17.4477 1 18 1C18.5523 1 19 1.44772 19 2V3C20.6569 3 22 4.34315 22 6V20C22 21.6569 20.6569 23 19 23H5C3.34315 23 2 21.6569 2 20V6C2 4.34315 3.34315 3 5 3H6V2C6 1.44772 6.44772 1 7 1ZM6 5H5C4.44772 5 4 5.44772 4 6V20C4 20.5523 4.44772 21 5 21H19C19.5523 21 20 20.5523 20 20V6C20 5.44772 19.5523 5 19 5V6C19 6.55228 18.5523 7 18 7C17.4477 7 17 6.55228 17 6V5H8V6C8 6.55228 7.55228 7 7 7C6.44772 7 6 6.55228 6 6V5ZM5 9C5 8.44772 5.44772 8 6 8L18 8C18.5523 8 19 8.44772 19 9C19 9.55229 18.5523 10 18 10L6 10C5.44772 10 5 9.55228 5 9ZM5 14C5 13.4477 5.44772 13 6 13H8C8.55228 13 9 13.4477 9 14C9 14.5523 8.55228 15 8 15H6C5.44772 15 5 14.5523 5 14ZM10 14C10 13.4477 10.4477 13 11 13H13C13.5523 13 14 13.4477 14 14C14 14.5523 13.5523 15 13 15H11C10.4477 15 10 14.5523 10 14ZM15 14C15 13.4477 15.4477 13 16 13H18C18.5523 13 19 13.4477 19 14C19 14.5523 18.5523 15 18 15H16C15.4477 15 15 14.5523 15 14ZM5 17C5 16.4477 5.44772 16 6 16H8C8.55228 16 9 16.4477 9 17C9 17.5523 8.55228 18 8 18H6C5.44772 18 5 17.5523 5 17ZM10 17C10 16.4477 10.4477 16 11 16H13C13.5523 16 14 16.4477 14 17C14 17.5523 13.5523 18 13 18H11C10.4477 18 10 17.5523 10 17ZM15 17C15 16.4477 15.4477 16 16 16H18C18.5523 16 19 16.4477 19 17C19 17.5523 18.5523 18 18 18H16C15.4477 18 15 17.5523 15 17Z" />
                    </svg>
                    <span>Добавить в календарь</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно листа ожидания -->
<div id="waitingListSuccessModal" class="result-modal">
    <div class="result-modal-content">
        <button id="closeWaitingListModal" class="but calendar-button">
            <img src="<?= THEME_URI; ?>/assets/img/onlineForm/close.svg" alt="">
        </button>
        <div class="result-modal-header">
            <div class="result-modal-ico"><img src="<?= THEME_URI; ?>/assets/img/onlineForm/ico7.svg" alt=""></div>
            <h3>Ваша заявка отправлена!</h3>
        </div>
        <div class="result-modal-body">
            <div class="result-modal-details">
                <div class="result-modal-detail">
                    <span class="result-modal-detail-label">Специалист:</span>
                    <span class="result-modal-detail-value" id="waitingSpecialistName"></span>
                </div>
                <div class="result-modal-detail">
                    <span class="result-modal-detail-label">Статус:</span>
                    <span class="result-modal-detail-value">Лист ожидания</span>
                </div>
            </div>
            <div class="waiting-list-message">
                📞 Мы свяжемся с вами в ближайшее время, чтобы уточнить детали и предложить доступное время для консультации.
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>