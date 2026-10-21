import axios from 'axios';
import { ref } from 'vue';
import type { SidangNolDocumentUrls } from './types';

export function useSidangNolDocuments() {
    const documentUrls = ref<SidangNolDocumentUrls>({});
    const isLoadingDocuments = ref(false);

    async function loadDocumentUrls(berkasId: number): Promise<SidangNolDocumentUrls> {
        isLoadingDocuments.value = true;

        try {
            const response = await axios.get<SidangNolDocumentUrls>(
                route('berkas-sidang-nol.get-uploads', berkasId),
            );

            documentUrls.value = response.data;
            return response.data;
        } finally {
            isLoadingDocuments.value = false;
        }
    }

    return {
        documentUrls,
        isLoadingDocuments,
        loadDocumentUrls,
    };
}
