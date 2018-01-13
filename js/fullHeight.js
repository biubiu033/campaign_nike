/*
 * 容器高度自适应(jQuery plugin)
 * Author：xiaohai
 * Date: 2014-03-07
 * Description: 容器高度小于窗口时自动全屏, 默认支持resize事件。
    例：$('div').fullHeight()
 */

(function($){

    $.fn.fullHeight = function(){
        var $this = this;
        pageHeightListener();
        $(window).on('resize.fullHeight', function(){
            pageHeightListener();
        })
        function pageHeightListener() {
            var $box = $this,
                boxHeight = $box.height(),
                windowHeight = $(window).height();
            if(boxHeight < windowHeight){
                $box.css('height', windowHeight + 'px');
            }
        }
        return this;
    };

})(jQuery);