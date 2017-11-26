$(document).ready(function () {
    $(document).on('mouseover','.nav-tabs > li:not(.active)',function () {
        var $this = $(this);
        $this.siblings('.active').addClass('opacity-line');
    });
    $(document).on('mouseleave','.nav-tabs > li:not(.active)',function () {
        var $this = $(this);
        $this.siblings().removeClass('opacity-line');
    });
    $(document).on('click','.nav-tabs > li',function () {
        var $this = $(this);
        $this.siblings().removeClass('opacity-line');
        $this.addClass('active');
    });

});
