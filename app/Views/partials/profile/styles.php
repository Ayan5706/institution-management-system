<style>
    .profile-container {
        max-width: 640px;
        margin: 20px 0;
    }

    .profile-card {
        padding: 20px;
        background: #f8fafc;
        border: 1px solid #dbe4f0;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .profile-section-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }

    .profile-row {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 16px;
        margin-bottom: 12px;
        align-items: center;
    }

    .profile-label {
        font-weight: 600;
        color: #0f172a;
    }

    .profile-value {
        color: #64748b;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: #0f172a;
        font-size: 0.9rem;
    }

    .form-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dbe4f0;
        border-radius: 8px;
        font-family: inherit;
        font-size: 0.9rem;
        background: #fff;
    }

    .form-input:focus {
        outline: 0;
        border-color: #2563eb;
        background: #f8fbff;
    }

    .form-input:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 10px 16px;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #0d9488);
        color: #fff;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-secondary {
        background: #fff;
        color: #0f172a;
        border: 1px solid #dbe4f0;
    }

    .btn-secondary:hover {
        background: #f8fbff;
        border-color: #2563eb;
    }

    .success-message {
        padding: 12px 16px;
        background: #d1fae5;
        border-left: 4px solid #10b981;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 0.9rem;
        color: #065f46;
        display: none;
    }


    .error-message {
        padding: 12px 16px;
        background: #fee2e2;
        border-left: 4px solid #dc2626;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 0.9rem;
        color: #7f1d1d;
        display: none;
    }

    @media (max-width: 640px) {
        .profile-row {
            grid-template-columns: 1fr;
            gap: 6px;
        }

        .button-group {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            text-align: center;
        }
    }
</style>
