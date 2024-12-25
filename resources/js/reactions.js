// التعامل مع التفاعلات
class ReactionHandler {
    constructor() {
        this.token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // تبديل حالة التفاعل
    async toggleReaction(commentId, type) {
        try {
            const response = await fetch('/api/reactions/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.token
                },
                body: JSON.stringify({
                    comment_id: commentId,
                    type: type
                })
            });

            const data = await response.json();
            if (data.success) {
                this.updateReactionUI(commentId, data.stats);
                return data.action; // 'added' or 'removed'
            }
        } catch (error) {
            console.error('Error toggling reaction:', error);
        }
    }

    // جلب إحصائيات التفاعلات
    async getReactionStats(commentId) {
        try {
            const response = await fetch(`/api/reactions/stats/${commentId}`);
            const data = await response.json();
            if (data.success) {
                this.updateReactionUI(commentId, data.stats);
            }
        } catch (error) {
            console.error('Error fetching reaction stats:', error);
        }
    }

    // تحديث واجهة المستخدم
    updateReactionUI(commentId, stats) {
        const reactionTypes = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
        const container = document.querySelector(`#reactions-${commentId}`);
        
        if (container) {
            reactionTypes.forEach(type => {
                const counter = container.querySelector(`.reaction-count-${type}`);
                if (counter) {
                    counter.textContent = stats[type] || 0;
                }
            });
        }
    }
}

// تهيئة معالج التفاعلات
document.addEventListener('DOMContentLoaded', () => {
    window.reactionHandler = new ReactionHandler();

    // إضافة مستمعي الأحداث لأزرار التفاعل
    document.querySelectorAll('.reaction-button').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const commentId = button.dataset.commentId;
            const type = button.dataset.type;
            const action = await window.reactionHandler.toggleReaction(commentId, type);
            
            // تحديث حالة الزر
            if (action === 'added') {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    });
});
