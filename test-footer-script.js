// Script de test pour vérifier l'affichage du footer
console.log('🔍 Test de visibilité du footer - Herime Academie');

// Fonction pour vérifier si le footer est visible
function checkFooterVisibility() {
    const footer = document.querySelector('.footer');
    if (!footer) {
        console.error('❌ Footer non trouvé sur la page');
        return false;
    }
    
    const footerRect = footer.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    
    // Vérifier si le footer est dans la zone visible
    const isVisible = footerRect.top < windowHeight && footerRect.bottom > 0;
    
    if (isVisible) {
        console.log('✅ Footer visible');
        console.log('📍 Position:', {
            top: footerRect.top,
            bottom: footerRect.bottom,
            height: footerRect.height
        });
    } else {
        console.error('❌ Footer non visible');
        console.log('📍 Position:', {
            top: footerRect.top,
            bottom: footerRect.bottom,
            height: footerRect.height
        });
    }
    
    return isVisible;
}

// Fonction pour vérifier les conflits de z-index
function checkZIndexConflicts() {
    const footer = document.querySelector('.footer');
    if (!footer) return;
    
    const footerZIndex = parseInt(window.getComputedStyle(footer).zIndex) || 0;
    console.log('🎯 Z-index du footer:', footerZIndex);
    
    // Vérifier les éléments avec z-index plus élevé
    const allElements = document.querySelectorAll('*');
    const conflictingElements = [];
    
    allElements.forEach(element => {
        const zIndex = parseInt(window.getComputedStyle(element).zIndex) || 0;
        const position = window.getComputedStyle(element).position;
        
        if (zIndex > footerZIndex && (position === 'fixed' || position === 'absolute')) {
            conflictingElements.push({
                element: element,
                zIndex: zIndex,
                position: position,
                className: element.className
            });
        }
    });
    
    if (conflictingElements.length > 0) {
        console.warn('⚠️ Éléments avec z-index plus élevé que le footer:');
        conflictingElements.forEach(item => {
            console.log(`- ${item.className} (z-index: ${item.zIndex}, position: ${item.position})`);
        });
    } else {
        console.log('✅ Aucun conflit de z-index détecté');
    }
}

// Fonction pour vérifier la structure du footer
function checkFooterStructure() {
    const footer = document.querySelector('.footer');
    if (!footer) return false;
    
    const requiredElements = [
        '.footer h5',
        '.footer a',
        '.footer .container',
        '.footer .row'
    ];
    
    let structureValid = true;
    requiredElements.forEach(selector => {
        const element = footer.querySelector(selector);
        if (!element) {
            console.error(`❌ Élément manquant: ${selector}`);
            structureValid = false;
        }
    });
    
    if (structureValid) {
        console.log('✅ Structure du footer valide');
    }
    
    return structureValid;
}

// Exécuter tous les tests
function runAllTests() {
    console.log('🚀 Démarrage des tests...');
    
    const footerVisible = checkFooterVisibility();
    const structureValid = checkFooterStructure();
    checkZIndexConflicts();
    
    if (footerVisible && structureValid) {
        console.log('🎉 Tous les tests sont passés - Footer fonctionnel');
    } else {
        console.error('💥 Certains tests ont échoué - Footer non fonctionnel');
    }
}

// Exécuter les tests au chargement de la page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runAllTests);
} else {
    runAllTests();
}

// Exporter les fonctions pour utilisation manuelle
window.FooterTester = {
    checkFooterVisibility,
    checkZIndexConflicts,
    checkFooterStructure,
    runAllTests
};


