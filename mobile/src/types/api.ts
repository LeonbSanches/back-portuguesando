export type LoginResponse = {
  token: string;
  user: {
    id: number;
    name: string;
    email: string;
    status: string;
  };
};

export type ProgressResponse = {
  data: {
    streak: {
      current_days: number;
      best_days: number;
      last_study_date: string | null;
    };
    daily_goal: {
      goal_date: string;
      target_questions: number;
      completed_questions: number;
      target_minutes: number;
      completed_minutes: number;
      status: string;
    };
    xp: {
      total: number;
      level: number;
      current_level_xp: number;
      next_level_xp: number;
      progress_percent_to_next_level: number;
      estimated_days_to_next_level: number | null;
    };
    consistency: {
      score: number;
      study_days_last_7_days: number;
      classification: "low" | "medium" | "high";
    };
    next_best_action: {
      type: string;
      message: string;
      priority: "low" | "medium" | "high";
      expires_at: string;
      cta: {
        route: string;
        label: string;
        payload: Record<string, unknown>;
      };
    };
    performance: {
      total_attempts: number;
      correct_attempts: number;
      accuracy_rate: number;
    };
  };
};
