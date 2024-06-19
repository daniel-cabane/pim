import { computed } from "vue"

export default function usePickWorkshopLg(workshop) {
    let currentLanguage = 'fr';

    const title = computed(() => {
        if (workshop.language == 'both') {
            return workshop.title[currentLanguage];
        }

        return workshop.title[workshop.language];
    });

    const description = computed(() => {
        if (workshop.language == 'both') {
            return workshop.description[currentLanguage];
        }

        return workshop.description[workshop.language];
    });

    return { title, description }

}