<template>
    <div class="background-waves">
        <div class="background-wave top-wave">
            <svg width="240" height="455" viewBox="0 0 240 455" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M240 0C1.52588e-05 153.367 240 316.936 1.52588e-05 455L1.52588e-05 0H240Z" fill="#FAD307"/>
            </svg>
        </div>
        <div class="background-wave bottom-wave">
            <svg width="479" height="709" viewBox="0 0 479 709" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 708.471C479 469.86 0 215.378 479 0.575562V708.471H0Z" fill="#FAD307"/>
            </svg>
        </div>
    </div>

    <div class="background-pikachu-face">
        <svg width="242" height="273" viewBox="0 0 242 273" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M50.0004 112.37C68.0707 118.449 86.0791 124.712 90.0001 113.057C93.9212 101.401 75.789 95.5061 57.7187 89.4269C39.6484 83.3477 19.6536 87.9718 19.2248 89.2464C18.796 90.521 31.93 106.29 50.0004 112.37Z" fill="#FAE307"/>
            <path d="M143.648 45.992C147.638 64.7517 151.438 83.5518 163.466 80.9935C175.494 78.4351 171.314 59.7158 167.324 40.9561C163.334 22.1964 149.154 7.23818 147.838 7.51795C146.523 7.79772 139.658 27.2323 143.648 45.992Z" fill="#FAE307"/>
            <g style="mix-blend-mode:darken">
            <circle cx="211.744" cy="196.664" r="23" transform="rotate(-21.4002 211.744 196.664)" fill="#EE3232"/>
            </g>
            <g style="mix-blend-mode:darken">
            <circle cx="113.983" cy="234.977" r="23" transform="rotate(-21.4002 113.983 234.977)" fill="#EE3232"/>
            </g>
            <path d="M138.706 204.881C138.706 204.881 145.923 208.497 149.275 207.183C152.999 205.724 155.465 198.313 156.396 197.948C157.327 197.583 164.637 201.163 167.896 199.885C171.155 198.608 174.086 191.015 174.086 191.015" stroke="#050505" stroke-width="3" stroke-miterlimit="5.75877" stroke-linecap="round"/>
            <line x1="153.842" y1="191.431" x2="156.031" y2="197.017" stroke="#050505"/>
            <path d="M154.831 191.58C154.716 192.341 153.82 192.692 153.218 192.212L145.096 185.729C144.495 185.248 144.639 184.297 145.355 184.016L155.031 180.224C155.748 179.943 156.5 180.543 156.385 181.305L154.831 191.58Z" fill="#050505"/>
            <circle cx="95.3615" cy="162.795" r="18" transform="rotate(-21.4002 95.3615 162.795)" fill="#050505"/>
            <circle cx="98.8812" cy="157.388" r="9.75" transform="rotate(-21.4002 98.8812 157.388)" fill="white"/>
            <circle cx="177.294" cy="130.685" r="18" transform="rotate(-21.4002 177.294 130.685)" fill="#050505"/>
            <circle cx="171.038" cy="129.109" r="9.75" transform="rotate(-21.4002 171.038 129.109)" fill="white"/>
        </svg>
    </div>
</template>

<style lang="scss" scoped>
    .background-wave {
        line-height: 0;
        max-width: 100%;
        overflow: hidden;
        position: fixed;

        &.bottom-wave {
            bottom: 0;
            right: 0;
        }

        &.top-wave {
            left: 0;
            top: 0;
        }
    }

    .background-pikachu-face {
        bottom: 0;
        line-height: 0;
        position: absolute;
        right: 0;

        svg {
            height: clamp(100px, 40vw, 273px);
            width: clamp(100px, 40vw, 242px);
        }
    }

</style>