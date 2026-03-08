<div
    x-data="{show:false,type:'success',message:''}"

    @toast.window="
    type=$event.detail.type;
    message=$event.detail.message;
    show=true;
    setTimeout(()=>show=false,3000)"

    class="fixed bottom-6 right-6 z-50">

    <div
        x-show="show"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-10 opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-10 opacity-0"
        class="pointer-events-auto px-5 py-3 rounded-xl shadow-lg text-white flex items-center gap-3"
        :class="{
        'bg-green-500': type==='success',
        'bg-red-500': type==='error',
        'bg-yellow-500': type==='warning'
        }">

        <i
            class="fa-solid"
            :class="{
            'fa-circle-check': type==='success',
            'fa-circle-xmark': type==='error',
            'fa-triangle-exclamation': type==='warning'
            }">
        </i>

        <span x-text="message"></span>

    </div>
</div>